# JvMTECH.SelectiveMixins Package for Neos CMS
[![Latest Stable Version](https://poser.pugx.org/jvmtech/selective-mixins/v/stable)](https://packagist.org/packages/jvmtech/selective-mixins)
[![License](https://poser.pugx.org/jvmtech/selective-mixins/license)](https://packagist.org/packages/jvmtech/selective-mixins)

> Create granular, reusable NodeTypes with ease. Best use with Props.Mixins per presentational components.

- **Load mixins multiple times** in the same NodeType

  ➔ *You've ever overwritten properties by choosing a not unique property name? Use our namespaces and ane the properties like what they are.*

- **Choose properties** you'd like to load

  ➔ *You don't need a property? So don't include, not just hide it.*

- **Extend labels** of props and groups recursively
  
  ➔ *You don't have to repeat the properties label text ever and ever again. Extend it.*

- **Merge groups** to match editor needs

  ➔ *Combine multiple props of multiple mixins for a new purpose? Do it.*

```yaml
# YOU WRITE..

'Vendor:Teaser':
  superTypes:
    'Neos.Neos:Content': true
  options:
    superTypes:
      'Vendor:Props.Link':
        link: true
      'Vendor:Props.Headline':
        headline: true
      'Vendor:Props.Image':
        desktop: true
        mobile: true
    mergeGroups:
      images:
        'Vendor:Props.Image':
          desktop: true
          mobile: true
  ui:
    label: 'Teaser'
    inspector:
      groups:
        images:
          label: 'Images'

# YOU GET..

'Vendor:Teaser':
  superTypes:
    'Neos.Neos:Content': true
  properties:
    linkHref:
      # ...
      ui:
        inspector:
          group: linkLink
    headlineText:
      # ...
    headlineTagName:
      # ...
      ui:
        inspector:
          group: headlineHeadline
    desktopAsset:
      # ...
      ui:
        inspector:
          group: images
    mobileAsset:
      # ...
      ui:
        inspector:
          group: images
  ui:
    label: 'Teaser'
    inspector:
      groups:
        linkLink:
          label: 'Link'
        headlineHeadline:
          label: 'Headline'
        images:
          label: 'Images'
```

## Schema

```yaml
# Native usage:
{Vendor:NodeType}:
  superTypes:
    {NodeTypeNameToMixinAsItIs}: true

# Usage with namespaced mixins:
{Vendor:NodeType}:
  options:
    superTypes:
      {NodeTypeNameToCopyFrom}:
        {NamespaceToPasteInto}: true
        {AnotherNamespaceToPasteInto}: true

# Usage with namespaced and selective mixins:
{Vendor:NodeType}:
  options:
    superTypes:
      {NodeTypeNameToCopyFrom}:
        {NamespaceToPasteInto}:
          {PropertyNameToConsider}: true

# Prefix and suffix group label of all namespaced properties:
{Vendor:NodeType}:
  options:
    superTypes:
      {NodeTypeNameToCopyFrom}:
        {NamespaceToPasteInto}: 'Group Prefix %s Suffix'

# Prefix and suffix all property inspector labels:
{Vendor:NodeType}:
  options:
    superTypes:
      {NodeTypeNameToCopyFrom}:
        {NamespaceToPasteInto}:
          '*': 'Label Prefix %s Suffix'

# Override the properties inspector label:
{Vendor:NodeType}:
  options:
    superTypes:
      {NodeTypeNameToCopyFrom}:
        {NamespaceToPasteInto}:
          {PropertyNameToConsider}: 'New Property Label'
          {PropertyNameToConsider}: 'Property Prefix %s Label'

# Merge Inspector Groups:
{Vendor:NodeType}:
  ui:
    inspector:
      groups:
        newGroup:
          label: 'A new Group'
  options:
    mergeGroups:
      {NewGroupName}:
        {NamespaceToInclude}: true
        {AnotherNamespaceToInclude}: true
      {NewGroupName}:
        {Namespace}:
          {GroupNameToInclude}: true
        {AnotherNamespace}:
          {GroupName}:
            {PropertyNameToInclude}: true
```

## Example A

Create a presentational frontend component:
```neosfusion
prototype(Vendor:Component.Link) < prototype(Neos.Fusion:Component) {
  text = ''
  link = ''
  
  renderer = afx`
    <a href={props.link}>{props.text}</div>
  `
}
```

Create a corresponding reusable Props Mixin which reflects the needed Props:
```yaml
'Vendor:Props.Link':
  abstract: true
  superTypes:
    'JvMTECH.SelectiveMixins:Data': true
  ui:
    inspector:
      groups:
        link:
          icon: link
          label: 'Link'
  properties:
    text:
      options:
        preset: 'rte.plaintext'
    link:
      options:
        preset: 'link.default'
      ui:
        inspector:
          group: link

```

And create a matching Props Fusion to load the component specific data:
```neosfusion
prototype(Vendor:Props.Link) < prototype(JvMTECH.SelectiveMixins:Data) {
  text = JvMTECH.SelectiveMixins:Data.PlainProperty.Text {
    property = 'text'
  }
  link = JvMTECH.SelectiveMixins:Data.PlainProperty.Link {
    property = 'link'
  }
}
```

Now create a Content NodeType where you reuse this Props-Mixin and -Fusion as often as you like - with different namespaces:
```yaml
'Vendor:Content.ThreeLinks':
  superTypes:
    'Neos.Neos:Content': true
  options:
    superTypes:
      'Vendor:Props.Link':
        left: 'Left %s'
        right: 'Right %s'
        bottom:
          link: true
  ui:
    label: 'Three Links'
    inspector:
      groups:
        bottomLink:
          label: 'Bottom Link'
```
```neosfusion
prototype(Vendor:Content.ThreeLinks) < prototype(Neos.Neos:ContentComponent) {
  left = Vendor:Props.Link {
    namespace = 'left'
    // result: {
    //   text: ...
    //   link: ...
    // }
  }
  right = Vendor:Props.Link {
    namespace = 'right'
    // result: {
    //   text: ...
    //   link: ...
    // }
  }
  bottom = Vendor:Props.Link {
    namespace = 'bottom'
    // result: {
    //   link: ...
    // }
  }

  renderer = afx`
    <div class="left-col">
      <Vendor:Component.Button {...props.left} />
    </div>
    <div class="right-col">
      <Vendor:Component.Button {...props.right} />
    </div>
    <Vendor:Component.Button {...props.bottom} text="Static Label" />
  `
}
```

The generated NodeType will look like:
```yaml
'Vendor:Content.ThreeLinks':
  superTypes:
    'Neos.Neos:Content': true
  ui:
    label: 'Three Links'
    inspector:
      groups:
        leftLink:
          icon: link
          label: 'Left Link'
        rightLink:
          icon: link
          label: 'Right Link'
        bottomLink:
          icon: link
          label: 'Bottom Link'
  properties:
    leftText:
      options:
        preset: 'rte.plaintext'
    leftLink:
      options:
        preset: 'link.default'
      ui:
        inspector:
          group: leftLink
    rightText:
      options:
        preset: 'rte.plaintext'
    rightLink:
      options:
        preset: 'link.default'
      ui:
        inspector:
          group: rightLink
    bottomLink:
      options:
        preset: 'link.default'
      ui:
        inspector:
          group: bottomLink
```

## Example B

Merging Inspector Groups to recombine Props-Mixins (only works with options.superTypes):
```
'Vendor:Content.Teaser':
  superTypes:
    'Neos.Neos:Content': true
  options:
    superTypes:
      'JvMTECH.Components:Props.Headline':
        headline:
          text: true
          tagName: true
      'JvMTECH.Components:Props.Button':
        button:
          link: true
    mergeGroups:
      teaserGroup:
        headline: true
        button: true
  ui:
    label: 'Teaser'
    inspector:
      groups:
        teaserGroup:
          label: 'Teaser'
```

The generated NodeType will look like:
```yaml
'Vendor:Content.Teaser':
  superTypes:
    'Neos.Neos:Content': true
  ui:
    label: 'Teaser'
    inspector:
      groups:
        teaserGroup:
          label: 'Teaser'
  properties:
    headlineText:
      options:
        preset: 'rte.headline'
      ui:
        inline:
          editorOptions:
            placeholder: 'JvMTECH.Base:Presets:rte.placeholder.short'
    headlineTagName:
      type: string
      defaultValue: 'h2'
      ui:
        label: 'SEO Tag'
        reloadIfChanged: true
        inspector:
          group: 'teaserGroup'
          editor: Neos.Neos/Inspector/Editors/SelectBoxEditor
          editorOptions:
            multiple: false
            allowEmpty: false
            values:
              h2:
                label: 'H2'
              h3:
                label: 'H3'
              h4:
                label: 'H4'
              h5:
                label: 'H5'
              p:
                label: 'p'
    link:
      options:
        preset: 'link.default'
      ui:
        inspector:
          group: 'teaserGroup'
```

## Tests

Run the following test in your project to make sure the Selective NodeType generation is still working as expected, even after upgrades:

```shell
cp DistributionPackages/JvMTECH.SelectiveMixins/Tests/Functional/Fixtures/NodeTypes.yaml DistributionPackages/JvMTECH.SelectiveMixins/Configuration/NodeTypes.Test.yaml;
FLOW_CONTEXT=Testing ./flow flow:cache:flush;
FLOW_CONTEXT=Testing ./bin/phpunit -c ./DistributionPackages/JvMTECH.SelectiveMixins/Tests/PhpUnit/FunctionalTests.xml;
rm DistributionPackages/JvMTECH.SelectiveMixins/Configuration/NodeTypes.Test.yaml;
```

## Installation

```
composer require jvmtech/selective-mixins
```

---

by [jvmtech.ch](https://jvmtech.ch)
