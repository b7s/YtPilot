<?php

declare(strict_types=1);

namespace YtPilot\Enums;

enum TwitterApi: string
{
    case Syndication = 'syndication';
    case GraphQL = 'graphql';
    case GraphQLLegacy = 'graphql-legacy';
}
