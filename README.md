# XPage
A project to create an Object Orient approach to polyglot XHTML5 page manipulation

At present there are two files to this project:

## AdvElement
AE extends SimpleXMLElement and adds a few "missing" features.

## AEWrapper
The wrapper is a pass through extension of AE and allows the apparent objects 
that are the system resource that is SXE to be a true object with tree awareness.

## Updates

Added a new hack of a method which copes with the fact that 

''' $XPage->page()->div 

is fine but

''' $XPage->page()->div[1]

Throws a pink fit.