<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Http;

/**
 * Keeps the GA4 API secret out of logs.
 *
 * The Measurement Protocol takes the secret as a query parameter, and HTTP
 * client exceptions quote the full request URL in their message, so a failed
 * request would otherwise write the secret to every log handler (and Sentry).
 *
 * @internal
 */
final class SecretRedactor
{
    public static function redact(string $text, string $secret): string
    {
        $text = preg_replace('/(api_secret=)[^&"\'\s]*/', '$1***', $text) ?? $text;

        if ('' === $secret) {
            return $text;
        }

        return str_replace([$secret, rawurlencode($secret)], '***', $text);
    }

    /**
     * Log context describing $e without the secret. The exception object
     * itself is left out on purpose: its message, its previous exceptions
     * and its trace arguments may all carry the secret.
     *
     * @return array{error: string, exception_class: class-string<\Throwable>, origin: string}
     */
    public static function logContext(\Throwable $e, string $secret): array
    {
        return [
            'error' => self::redact($e->getMessage(), $secret),
            'exception_class' => $e::class,
            'origin' => $e->getFile().':'.$e->getLine(),
        ];
    }
}
