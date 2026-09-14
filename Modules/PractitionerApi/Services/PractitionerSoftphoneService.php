<?php

namespace Modules\PractitionerApi\Services;

use Modules\OnlineConsultation\Models\ConsultationPractitioner;
use Modules\OnlineConsultation\Models\ConsultationSetting;

class PractitionerSoftphoneService
{
    /**
     * Build the complete SIP registration contract consumed by the mobile softphone.
     *
     * @return array{
     *     configured: bool,
     *     server_address: string|null,
     *     server_host: string|null,
     *     server_port: int,
     *     transport: string,
     *     extension: string|null,
     *     username: string|null,
     *     password: string|null,
     *     missing_fields: list<string>
     * }
     */
    public function for(ConsultationPractitioner $practitioner): array
    {
        $settings = ConsultationSetting::current();
        $serverAddress = $this->nullableString($settings->voip_host);
        $serverHost = $this->serverHost($serverAddress);
        $extension = $this->nullableString($practitioner->extension);
        $username = $this->nullableString($practitioner->sip_username);
        $password = $this->nullableString($practitioner->sip_secret);
        $transport = strtolower($this->nullableString($settings->voip_transport) ?? 'tls');

        if (! in_array($transport, ['tls', 'tcp', 'udp'], true)) {
            $transport = 'tls';
        }

        $required = [
            'server_host' => $serverHost,
            'extension' => $extension,
            'username' => $username,
            'password' => $password,
        ];
        $missingFields = array_keys(array_filter($required, fn (?string $value): bool => $value === null));

        return [
            'configured' => $missingFields === [],
            'server_address' => $serverAddress,
            'server_host' => $serverHost,
            'server_port' => max(1, min(65535, (int) ($settings->voip_port ?: 5061))),
            'transport' => $transport,
            'extension' => $extension,
            'username' => $username,
            'password' => $password,
            'missing_fields' => $missingFields,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function serverHost(?string $address): ?string
    {
        if ($address === null) {
            return null;
        }

        $host = parse_url($address, PHP_URL_HOST);
        if (is_string($host) && $host !== '') {
            return $host;
        }

        $host = parse_url('sip://'.$address, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : $address;
    }
}
