<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Tests\Support;

use Psr\Log\AbstractLogger;

/**
 * Keeps every record, so a test can assert what reached the log.
 */
final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{level: mixed, message: string, context: array}> */
    public array $records = [];

    public function log($level, $message, array $context = []): void
    {
        $this->records[] = ['level' => $level, 'message' => (string) $message, 'context' => $context];
    }

    /**
     * Everything a log handler could write: messages, context values and
     * the messages of any exception passed in the context.
     */
    public function dump(): string
    {
        $out = '';
        foreach ($this->records as $record) {
            $out .= $record['message']."\n";
            array_walk_recursive($record['context'], function ($value) use (&$out): void {
                for ($e = $value; $e instanceof \Throwable; $e = $e->getPrevious()) {
                    $out .= $e->getMessage()."\n";
                }
                if (is_scalar($value) || $value instanceof \Stringable) {
                    $out .= $value."\n";
                }
            });
        }

        return $out;
    }
}
