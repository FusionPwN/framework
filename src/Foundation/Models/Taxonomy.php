<?php

declare(strict_types=1);

/**
 * Contains the Taxonomy class.
 *
 * @copyright   Copyright (c) 2020 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2020-12-19
 *
 */

namespace Vanilo\Foundation\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Vanilo\Category\Models\Taxonomy as BaseTaxonomy;
use Vanilo\Contracts\HasImages;
use Vanilo\Foundation\Traits\LoadsMediaConversionsFromConfig;
use Vanilo\Support\Traits\HasImagesFromMediaLibrary;

class Taxonomy extends BaseTaxonomy implements HasMedia, HasImages
{
    use InteractsWithMedia;
    use HasImagesFromMediaLibrary;
    use LoadsMediaConversionsFromConfig;

    public function registerMediaConversions(Media $media = null): void
    {
        $this->loadConversionsFromVaniloConfig();
    }
}
