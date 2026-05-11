<?php

declare(strict_types=1);

namespace Afterchange\Template\Models;

use DateTime;

class OAuthClientRedirect extends Model
{
    public string $client_internal_id;
    public string $redirect_uri;
}
