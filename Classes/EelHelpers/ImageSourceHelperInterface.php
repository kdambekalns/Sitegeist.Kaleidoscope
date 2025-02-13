<?php
namespace Sitegeist\Kaleidoscope\EelHelpers;

use Neos\Eel\ProtectedContextAwareInterface;

interface ImageSourceHelperInterface extends ProtectedContextAwareInterface
{
    public function setWidth(int $width = null, bool $preserveAspect = false) : ImageSourceHelperInterface;

    public function setHeight(int $height = null, bool $preserveAspect = false) : ImageSourceHelperInterface;

    public function setDimensions(int $width = null, int $height = null) : ImageSourceHelperInterface;

    public function applyPreset(string $name) : ImageSourceHelperInterface;

    public function src() : string;

    public function srcset($mediaDescriptors) : string;

    public function __toString() : string;
}
