<?php

declare(strict_types=1);

namespace YtPilot\Enums;

enum ProxyProtocol: string
{
    case Http = 'http';
    case Https = 'https';
    case Socks4 = 'socks4';
    case Socks4a = 'socks4a';
    case Socks5 = 'socks5';
    case Socks5h = 'socks5h';
}
