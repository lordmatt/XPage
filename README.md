# XPage
A project to create an Object Orient approach to polyglot XHTML5 page manipulation

For suggested uses and in depth documentation see the [wiki](https://github.com/lordmatt/XPage/wiki)

At present there are two additional files to this project:

## AdvElement
AE extends SimpleXMLElement and adds a few "missing" features.

## AEWrapper
The wrapper is a pass through extension of AE and allows the apparent objects 
that are the system resource that is SXE to be a true object with tree awareness.

## Updates

Added a new hack of a method which copes with the fact that 

     $XPage->page()->div 

is fine but

     $XPage->page()->div[1]

Throws a pink fit.