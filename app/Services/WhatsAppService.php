<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente del gateway Evolution API (WhatsApp vía Baileys, docker/evolution).
 *
 * Mantiene la misma interfaz que usaba la pantalla de Configuración:
 * getStatus(), getQr(), sendMessage() y disconnect().
 */
class WhatsAppService
{
    protected ?string $url;

    protected ?string $apiKey;

    protected string $instance;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.evolution.url'), '/') ?: null;
        $this->apiKey = config('services.evolution.api_key');
        $this->instance = (string) config('services.evolution.instance', 'laabuela');
    }

    /**
     * Estado de la conexión en el formato que espera la pantalla de Configuración.
     */
    public function getStatus(): array
    {
        $state = $this->connectionState();

        if ($state === 'open') {
            return [
                'success' => true,
                'status' => 'connected',
                'connected' => true,
                'user' => $this->connectedUser(),
            ];
        }

        if (in_array($state, ['unconfigured', 'unreachable'], true)) {
            return [
                'success' => false,
                'status' => 'offline',
                'connected' => false,
                'message' => $state === 'unconfigured'
                    ? 'El gateway de WhatsApp no está configurado (EVOLUTION_API_KEY).'
                    : 'El gateway de WhatsApp (Evolution API) no está en ejecución.',
            ];
        }

        // missing / close / connecting: se necesita escanear un QR
        $qr = $this->getQr();

        return [
            'success' => true,
            'status' => ! empty($qr['qr_image']) ? 'qr_ready' : 'connecting',
            'connected' => false,
            'qr_image' => $qr['qr_image'] ?? null,
        ];
    }

    /**
     * Devuelve el QR actual (imagen base64). Crea la instancia si todavía no existe.
     */
    public function getQr(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'status' => 'offline', 'message' => 'El gateway de WhatsApp no está configurado.'];
        }

        // Evita pedir un QR nuevo en cada consulta de la pantalla (se refresca solo)
        $qr = Cache::remember('whatsapp_qr_'.$this->instance, 8, fn () => $this->requestQr());

        if ($qr) {
            return ['success' => true, 'status' => 'qr_ready', 'qr_image' => $qr];
        }

        return [
            'success' => true,
            'status' => 'connecting',
            'message' => 'Generando código QR, por favor aguarda unos segundos...',
        ];
    }

    /**
     * Envía un mensaje de texto al número indicado.
     */
    public function sendMessage(string $phone, string $message): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'El gateway de WhatsApp no está configurado.'];
        }

        $number = $this->formatNumber($phone);
        if (! $number) {
            return ['success' => false, 'error' => 'Número de teléfono inválido.'];
        }

        if ($this->connectionState() !== 'open') {
            return [
                'success' => false,
                'error' => 'El servicio de WhatsApp no está conectado. Escanea el código QR en la configuración.',
            ];
        }

        try {
            $response = $this->client()
                ->timeout(30)
                ->post("/message/sendText/{$this->instance}", [
                    'number' => $number,
                    'text' => $message,
                    // Sin 'delay': evita que Evolution emita presencia "escribiendo…" en cada envío
                    'linkPreview' => false,
                ]);

            $payload = $response->json() ?? [];

            if ($response->successful()) {
                return [
                    'success' => true,
                    'messageId' => $payload['key']['id'] ?? 'unknown',
                    'message' => 'Mensaje enviado con éxito.',
                ];
            }

            $raw = (string) json_encode($payload['response']['message'] ?? $payload['message'] ?? $payload, JSON_UNESCAPED_UNICODE);
            Log::warning('Evolution API rechazó el mensaje: '.$raw);

            return [
                'success' => false,
                'error' => str_contains($raw, '"exists":false')
                    ? 'Ese número no tiene WhatsApp registrado.'
                    : 'Error del gateway ('.$response->status().'): '.mb_substr($raw, 0, 300),
            ];
        } catch (\Throwable $e) {
            Log::error('Fallo al despachar mensaje WhatsApp: '.$e->getMessage());

            return ['success' => false, 'error' => 'No se pudo comunicar con el servicio de WhatsApp.'];
        }
    }

    /**
     * Cierra la sesión vinculada para poder escanear otro QR.
     */
    public function disconnect(): array
    {
        if ($this->isConfigured()) {
            try {
                $response = $this->client()->delete("/instance/logout/{$this->instance}");
                Cache::forget('whatsapp_qr_'.$this->instance);

                if ($response->successful() || $response->status() === 404) {
                    return ['success' => true, 'message' => 'Sesión de WhatsApp cerrada con éxito. Generando nuevo QR...'];
                }
            } catch (\Throwable $e) {
                Log::warning('Evolution API: error al desconectar: '.$e->getMessage());
            }
        }

        return ['success' => false, 'error' => 'No se pudo desconectar la sesión.'];
    }

    protected function isConfigured(): bool
    {
        return ! empty($this->url) && ! empty($this->apiKey);
    }

    /**
     * open | connecting | close | missing | unconfigured | unreachable
     */
    protected function connectionState(): string
    {
        if (! $this->isConfigured()) {
            return 'unconfigured';
        }

        try {
            $response = $this->client()->get("/instance/connectionState/{$this->instance}");

            if ($response->status() === 404) {
                return 'missing';
            }

            return $response->json('instance.state') ?? $response->json('state') ?? 'close';
        } catch (\Throwable $e) {
            Log::warning('Evolution API no responde: '.$e->getMessage());

            return 'unreachable';
        }
    }

    protected function requestQr(): ?string
    {
        try {
            if ($this->connectionState() === 'missing') {
                $created = $this->client()->post('/instance/create', [
                    'instanceName' => $this->instance,
                    'qrcode' => true,
                    'integration' => 'WHATSAPP-BAILEYS',
                ]);

                $this->applySettings();

                if ($qr = $created->json('qrcode.base64')) {
                    return $qr;
                }
            }

            return $this->client()->get("/instance/connect/{$this->instance}")->json('base64');
        } catch (\Throwable $e) {
            Log::warning('Evolution API: no se pudo obtener el QR: '.$e->getMessage());

            return null;
        }
    }

    /**
     * alwaysOnline=false evita que la sesión vinculada figure "en línea",
     * lo que bloquea las notificaciones push del teléfono.
     */
    protected function applySettings(): void
    {
        try {
            $this->client()->post("/settings/set/{$this->instance}", [
                'rejectCall' => false,
                'msgCall' => '',
                'groupsIgnore' => true,
                'alwaysOnline' => false,
                'readMessages' => false,
                'readStatus' => false,
                'syncFullHistory' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Evolution API: no se pudieron aplicar los ajustes: '.$e->getMessage());
        }
    }

    protected function connectedUser(): array
    {
        try {
            $data = $this->client()->get('/instance/fetchInstances', ['instanceName' => $this->instance])->json();
            $info = is_array($data) ? ($data[0] ?? $data) : [];
            $jid = $info['ownerJid'] ?? ($info['instance']['owner'] ?? '');

            return [
                'id' => explode('@', (string) $jid)[0],
                'name' => $info['profileName'] ?? ($info['instance']['profileName'] ?? 'Rotisería La Abuela'),
            ];
        } catch (\Throwable $e) {
            return ['id' => '', 'name' => 'Rotisería La Abuela'];
        }
    }

    /**
     * Normaliza un número argentino al formato internacional de WhatsApp (549...).
     */
    protected function formatNumber(string $rawPhone): ?string
    {
        $clean = preg_replace('/\D/', '', $rawPhone);
        if ($clean === '' || $clean === null) {
            return null;
        }

        if (str_starts_with($clean, '0')) {
            $clean = substr($clean, 1);
        }

        if (strlen($clean) === 10) {
            $clean = '549'.$clean;
        } elseif (str_starts_with($clean, '54') && ! str_starts_with($clean, '549') && strlen($clean) === 12) {
            $clean = '549'.substr($clean, 2);
        }

        return $clean;
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl((string) $this->url)
            ->withHeaders(['apikey' => (string) $this->apiKey])
            ->acceptJson()
            ->timeout(10);
    }
}
