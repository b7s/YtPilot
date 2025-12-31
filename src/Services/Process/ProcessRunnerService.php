<?php

declare(strict_types=1);

namespace YtPilot\Services\Process;

use Symfony\Component\Process\Process;
use YtPilot\Config;
use YtPilot\Exceptions\ProcessFailedException;

final class ProcessRunnerService
{
    /**
     * @param list<string> $command
     */
    public function run(
        array $command,
        ?string $cwd = null,
        ?int $timeout = null,
        ?callable $outputCallback = null,
    ): ProcessResult {
        $timeout ??= Config::get('timeout', 300);

        $process = new Process($command, $cwd);
        $process->setTimeout($timeout);

        if ($outputCallback !== null) {
            $process->run(function ($type, $buffer) use ($outputCallback): void {
                $outputCallback($type === Process::ERR ? 'err' : 'out', $buffer);
            });
        } else {
            $process->run();
        }

        return new ProcessResult(
            success: $process->isSuccessful(),
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
            exitCode: $process->getExitCode() ?? 1,
            command: implode(' ', $command),
        );
    }

    /**
     * @param list<string> $command
     */
    public function runOrFail(
        array $command,
        ?string $cwd = null,
        ?int $timeout = null,
        ?callable $outputCallback = null,
    ): ProcessResult {
        $result = $this->run($command, $cwd, $timeout, $outputCallback);

        if (!$result->success) {
            throw ProcessFailedException::fromProcess(
                $result->command,
                $result->output,
                $result->errorOutput,
                $result->exitCode,
            );
        }

        return $result;
    }

    /**
     * @param list<string> $command
     */
    public function runAsync(
        array $command,
        ?string $cwd = null,
        ?int $timeout = null,
    ): Process {
        $timeout ??= Config::get('timeout', 300);

        $process = new Process($command, $cwd);
        $process->setTimeout($timeout);
        $process->start();

        return $process;
    }
}

final readonly class ProcessResult
{
    public function __construct(
        public bool $success,
        public string $output,
        public string $errorOutput,
        public int $exitCode,
        public string $command,
    ) {}
}
