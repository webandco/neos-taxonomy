Neos Taxonomy Package
=========================================================

This package provides taxonomy functionality for Neos CMS.

## Structure
### Document NodeTypes
* DocumentTaxonomyStorage:
    Taxonomy root node (entry point)
    
* DocumentTaxonomyVocabulary:
    You can specify one or many vocabularies with individual access rights or behavior.
    
    eg. "Taxonomy" for free tagging
    
    eg. "Location" to provide structured taxonomies like country, state, city, ...
    
* DocumentTaxonomy:
    Create new nodes and use them as taxonomies right away. 
    
* DocumentTaxonomyAbstract (for extending custom nodeTypes)
    Extend custom nodetypes for more specific FlowQuery expressions.

### Content NodeTypes
* TaxonomyList:
    A simple view you can use to show a list of nodes matching taxonomies utilized by the "intersect" FlowQuery operator.  

### Abstract NodeTypes
* Webandco.Taxonomy:Taxonomy:
    Every nodeType inheriting Webandco.Taxonomy:Taxonomy will be available in the inspector - eg. utilized in the TaxonomyList yaml definition.
     
### Intersect FlowQuery Operation
In order to find nodes use the provided intersect() FlowQuery Operator. Like with all FlowQuery operators chaining is possible. In the List.ts2 file you will find a working example.

#### Parameters
- **type**: 'property' | 'node' - Note: if you choose node, for now just nodeType property is supported **(todo)**
- **property**: eg. 'taxonomies'
- **taxonomies**: eg 'top10, city,...' 

### getTaxonomies FlowQuery Operation
In oder to get all taxonomies related to a node you can use the getTaxonomies operation.

#### Parameters
- **vocabulary**: restrict list to a specific vocabulary (uriPathSegment)

```
debug = Neos.Fusion:Debug {
    value = ${q(node).getTaxonomies())}
} 
```

### Eel Helper
There is a Eel helper available to convert a taxonomy node array to a string. In order to do that the helper will extract the uriPathSegment as a comma separated list. If you define a defaultDimensionPreset it will fetch the uriPathSegment from the specified dimension.

```
debug = Neos.Fusion:Debug {
    value = ${Webandco.Taxonomy.toString(q(node).getTaxonomies().get())}
}
``` 

## Setup
* Download the package via composer
* Create the basic structure
    * Storage
        * Vocabulary
            * Taxonomy
                * (Taxonomy)
                
* Create a TaxonomyList content node and you are ready to go.



### Examples
**List.ts2 implementation**
```
${q(site).find('[instanceof Neos.Neos:Document]').filter('[taxonomies]').intersect('property', 'taxonomies', node.properties.taxonomies)}
```
**Chaining implementation**
```
${q(site).find('[instanceof Neos.Neos:Document]').filter('[taxonomies]').intersect('property', 'taxonomies', node.properties.taxonomies).intersect('node', 'nodeType', node.properties.locations)}
```

## TODO's
* Roadmap
* Performance 
* Testing

## Pull Requests are welcome!!
