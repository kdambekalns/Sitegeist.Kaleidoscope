# Sitegeist.Kaleidoscope

## Responsive Images for Neos - with Atomic.Fusion & Monocle in mind

This package implements responsive-images for Neos for beeing used via Fusion.

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource {
    width = 800
    height = 200
}

renderer = afx`
    <Sitegeist.Kaleidoscope:Image
        imageSource={props.imageSource}
        srcset="320w, 400w, 600w, 800w, 1000w, 1200w, 1600w"
        sizes="(min-width: 800px) 1000px, (min-width: 480px) 800px, (min-width: 320px) 440px, 100vw"
    />
`
```

By separating the aspects of image-definition, size-constraining and  rendering
we enable the separation of those aspects into different fusion-components.

We want to help implementing responsive-images in the context of atomic-fusion
and enable previewing fusion-components and their full responsive behavior in the
Sitegeist.Monocle living styleguide.

Sitegeist.Kaleidoscope comes with four Fusion-ImageSources:

- Sitegeist.Kaleidoscope:AssetImageSource: Images uploaded by Editors
- Sitegeist.Kaleidoscope:DummyImageSource: Dummyimages created by a local service
- Sitegeist.Kaleidoscope:ResourceImageSource: Static resources from Packages
- Sitegeist.Kaleidoscope:UriImageSource: any Url

### Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de
* Wilhelm Behncke - behncke@sitegeist.de

*The development and the public-releases of this package is generously sponsored
by our employer http://www.sitegeist.de.*

## Installation

Sitegeist.Kaleidoscope is available via packagist run `composer require sitegeist/kaleidoscope`.
We use semantic-versioning so every breaking change will increase the major-version number.

## Usage

## Image/Picture FusionObjects

The Kaleidoscope package integrates two main fusion-objects that an render
the given ImageSource as `img`- or `picture`-tag.

### `Sitegeist.Kaleidoscope:Image`

Render an `img`-tag with optional `srcset` based on `sizes` or `resolutions`.

Props:

- `imageSource`: the imageSource to render
- `srcset`: media descriptors like '1.5x' or '600w' of the default image (string ot array)
- `sizes`: sizes attribute of the default image (string ot array)
- `alt`: alt-attribute for the img tag
- `title`: title attribute for the img tag
- `class`: class attribute for the img tag

#### Image with srcset in multiple resolutions:

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource

renderer = afx`
    <Sitegeist.Kaleidoscope:Image
        imageSource={props.imageSource}
        srcset="1x, 2x, 3x"
        />
`
```
will render as:

```
<img src="_baseurl_" srcset="_url_1_ 1x, _url_2_ 2x, _url_3_ 3x">
```

#### Image with srcset in multiple sizes:

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource

renderer = afx`
    <Sitegeist.Kaleidoscope:Image
        imageSource={props.imageSource}
        srcset="320w, 400w, 600w, 800w, 1000w, 1200w, 1600w"
        sizes="(min-width: 800px) 1000px, (min-width: 480px) 800px, (min-width: 320px) 440px, 100vw"
        />
`
```

will render as:

```
<img
    src="_baseurl_"
    srcset="_url_1_ 320w, _url_2_ 400w, _url_3_ 600w, _url_4_ 800w, _url_5_ 1000w, _url_6_ 1200w, _url_7_ 1600w"
    sizes="(min-width: 800px) 1000px, (min-width: 480px) 800px, (min-width: 320px) 440px, 100vw"
/>
```

### `Sitegeist.Kaleidoscope:Picture`
`
Render a `picture`-tag with various sources.

Props:
- `imageSource`: the imageSource to render
- `sources`: an array of source definitions that supports the following keys
   - `media`: the media query of this source
   - `imageSource`: alternate image-source for art direction purpose
   - `srcset`: media descriptors like '1.5x' or '600w' (string ot array)
   - `sizes`: sizes attribute (string ot array)
- `srcset`: media descriptors like '1.5x' or '600w' of the default image (string ot array)
- `sizes`: sizes attribute of the default image (string ot array)
- `alt`: alt-attribute for the picture tag
- `title`: title attribute for the picture tag
- `class`: class attribute for the picture tag

```
imageSource = Sitegeist.Kaleidoscope:DummyImageSource
sources = Neos.Fusion:RawArray {
    large = Neos.Fusion:RawArray {
        srcset = '1x, 1.5x, 2x'
        media = 'screen and (min-width: 1600px)'
    }

    small = Neos.Fusion:RawArray {
        srcset = '320w, 480w, 800w'
        sizes = '(max-width: 320px) 280px, (max-width: 480px) 440px, 100vw'
        media = 'screen and (max-width: 1599px)'
    }

    print = Neos.Fusion:RawArray {
        imageSource = Sitegeist.Kaleidoscope:DummyImageSource {
            text = "im am here for printing"
        }
        media = 'print'
    }
}

renderer = afx`
    <Sitegeist.Kaleidoscope:Picture imageSource={props.imageSource} sources={props.sources} />
`
```

will render as:

```
<picture>
  <source
    srcset="_large_url_1_ 1x, _large_url_2_ 1.5x, _large_url_3_ 2x"
    media="screen and (min-width: 1600px)"
    />
  <source
    srcset="_small_url_1_ 320w, _small_url_2_ 480w, _small_url_3_ 800w, _small_url_4_ 1000w"
    sizes="(max-width: 320px) 280px, (max-width: 480px) 440px, 800px"
    media="screen and (max-width: 1599px)"
    />
  <source
    srcset="_print_url_1_"
    media="print"
    />
  <img src="_base_url_">
</picture>
```

## Responsive Images with AtomicFusion-Components and Sitegeist.Monocle

```
prototype (Vendor.Site:Component.ResponsiveKevisualImage) < prototype(Neos.Fusion:Component) {

    #
    # Use the DummyImageSource inside the styleguide
    #
    @styleguide {
        props {
            imageSource = Sitegeist.Kaleidoscope:DummyImageSource
        }
    }

    #
    # Enforce the dimensions of the passed images by cropping to 1600 x 800
    #
    imageSource = null
    imageSource.@process.enforeDimensions = ${value ? value.setWidth(1600).setHeight(900) : null}

    renderer = afx`
        <Sitegeist.Kaleidoscope:Image imageSource={props.imageSource} srcset="1x, 1.5x, 2x" />
    `
}
```

Please note that the enforced dimensions are applied in the presentational component.
The dimension enforcement is applied to the DummySource aswell as to the AssetSource
which will be defined by the integration.

The integration of the component above as content-element works like this:

```
prototype (Vendor.Site:Content.ResponsiveKevisual) < prototype(Neos.Neos:ContentComponent) {
    renderer = Vendor.Site:Component.ResponsiveKevisualImage {
        imageSource = Sitegeist.Kaleidoscope:AssetImageSource {
            asset = ${q(node).property('image')}
        }
    }
}
```

This shows that integration-code dos not need to know the required image dimensions or wich
variants are needed. This frontend know-how is now encapsulated into the presentational-component.

## ImageSource FusionObjects

The package contains ImageSource-FusionObjects that encapsulate the intention to
render an image. ImageSource-Objects return Eel-Helpers that allow to
enforcing the rendered dimensions later in the rendering process.

Note: The settings for `width`, `height` and `preset can be defined via fusion
but can also applied on the returned object. This will override the fusion-settings.

All ImageSources support the following fusion properties:

- `preset`: Set width and/or height via named-preset from Settings `Neos.Media.thumbnailPresets` (default null, settings below override the preset)
- `width`: Set the intended width (default null)
- `height`: Set the intended height (default null)

### `Sitegeist.Kaleidoscope:AssetImageSource`

Arguments:

- `asset`: An image asset that shall be rendered (defaults to the context value `asset`)
- `async`: Defer image-rendering until the image is actually requested by the browser (default true)
- `preset`, `width` and `height` are supported as explained above

### `Sitegeist.Kaleidoscope:DummyImageSource`

Arguments:
- `baseWidth`: The default width for the image before scaling (default = 600)
- `baseHeight`: The default height for the image before scaling (default = 400)
- `backgroundColor`: The background color of the dummyimage (default = '999')
- `foregroundColor`: The foreground color of the dummyimage (default = 'fff')
- `text`: The text that is rendered on the image (default = null, show size)
- `preset`, `width` and `height` are supported as explained above


### `Sitegeist.Kaleidoscope:UriImageSource`

Arguments:
- `uri`: The uri that will be rendered
- !!! `preset`, `width` and `height` have no effect on this ImageSource

### `Sitegeist.Kaleidoscope:ResourceImageSource`

Arguments:
- `package`: The package key (e.g. `'My.Package'`) (default = false)
- `path`: Path to resource, either a path relative to `Public` and `package` or a `resource://` URI (default = null)
- !!! `preset`, `width` and `height` have no effect on this ImageSource

## ImageSource EEl-Helpers

The ImageSource-helpers are created by the fusion-objects above and are passed to a
rendering component. The helpers allow to set or override the intended
dimensions and to render the `src` and `srcset`-attributes.

Methods of ImageSource-Helpers that are accessible via EEL:

- `applyPreset( string )`: Set width and/or height via named-preset from Settings `Neos.Media.thumbnailPresets`
- `setWidth( integer $width, bool $preserveAspect = false )`: Set the intend width modify height aswell if 
- `setHeight( integer $height, bool $preserveAspect = false )`: Set the intended height
- `setDimensions( integer, interger)`: Set the intended width and height
- `src ()` : Render a src attribute for the given ImageSource-object
- `srcset ( array of descriptors )` : render a srcset attribute for the ImageSource with given media descriptors like `2.x` or `800w`

Note: The Eel-helpers cannot be created directly. They have to be created
by using the `Sitegeist.Kaleidoscope:AssetImageSource` or
`Sitegeist.Kaleidoscope:DummyImageSource` fusion-objects.

### Examples

Render an `img`-tag with `src` and a `srcset` in multiple resolutions:

```
    imageSource = Sitegeist.Kaleidoscope:DummyImageSource
    renderer = afx`
        <img
            src={props.imageSource}
            srcset={props.imageSource.srcset('1x, 1.5x, 2x')}
        />
    `
```

Render an `img`-tag with `src` plus `srcset` and `sizes`:

```
    imageSource = Sitegeist.Kaleidoscope:DummyImageSource
    renderer = afx`
        <img
            src={props.imageSource}
            srcset={props.imageSource.srcset('400w, 600w, 800w')}
            sizes="(max-width: 320px) 280px, (max-width: 480px) 440px, 800px"
        />
    `
```
Render a `picture`-tag with multiple `source`-children and an `img`-fallback :

```
    imageSource = Sitegeist.Kaleidoscope:DummyImageSource
    renderer = afx`
        <picture>
            <source srcset={props.imageSource.setWidth(400).setHeight(400)} media="(max-width: 799px)" />
            <source srcset={props.imageSource.srcset('400w, 600w, 800w')} media="(min-width: 800px)" />
            <img src={props.imageSource} />
        </picture>
    `
```

In this example devices smaller than 800px will show a 400x400 square image,
while larger devices will render a multires-source in the orginal image dimension.

## Contribution

We will gladly accept contributions. Please send us pull requests.
