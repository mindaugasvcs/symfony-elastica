<?php

declare(strict_types=1);

namespace App\Controller\Snippet;

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

/**
 * I am using a PHP trait to isolate each snippet in a file.
 * This code should be called from a Symfony controller extending AbstractController (as of Symfony 4.2)
 * or Symfony\Bundle\FrameworkBundle\Controller\Controller (Symfony <= 4.1).
 * Services are injected in the main controller constructor.
 *
 * @property KernelInterface $kernel
 *
 * @see https://github.com/sensiolabs/ansi-to-html
 */
trait Snippet8Trait
{
    public function snippet8(): void
    {
        $process = new Process([
            'make',
            '-f',
            $this->kernel->getProjectDir().'/Makefile',
        ]);
        $process->run();

        echo (new AnsiToHtmlConverter())->convert($process->getOutput()); // That's it! 😁
    }
}