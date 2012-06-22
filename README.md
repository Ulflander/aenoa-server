## What is Aenoa Server?

Aenoa Server is PHP MVC framework to develop quickly and easily your web project.

## Documentation

Find documentation at http://www.aenoa-systems.com/docs/aenoa-server/

## Current version

Current version is 1.0.6

## Getting old version (1.0) of Aenoa Server

Clone Aenoa Server repo, then checkout the 1.0 branch

 git clone https://github.com/aenoa/aenoa-server.git
 git checkout 1.0

## Roadmap for Aenoa Server 1.1 (Codename: Alastor)

- Remove all deprecated APIs
- Add namespaces, aenoa base named
- Remove scaffolding things from DatabaseController
- rename and refactor AeAutoTable and AeAutoForm
- rename doController and split it (modify elements that calls it)
- activate new routes, conf, rights and transliteration directives via conf files in "app" app folder
- delete all unused classes and files
- Refactor static server detection (debug mode)
- Refactor I18n (), rename "lang" to "locale" everywhere, formalize locales as l10n classes
- Rename AeSeoFriend to SEOHTMLHelper
- Rename AeSocialShare to SocialHTMLHelper
- Extends all html helpers from an abstract HTMLHelperBase
- Rename all Ae* classes to * class name
- rename DBValidator to DBRegexps
- For fields definition :
	- now two (or more ?) ways to define fields: via structures and in tasks
	- todo: formalize fields (DBField)
	- Remove types from DBTable and set them in DBField
- Check and change all App::do500 calls
- Add users management funcs
- Change all trigger_errors to throw new ErrorException, make an error manager

## Licence

ACF is MIT licensed.

Copyright (c) 2011 Aenoa Systems

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
IN THE SOFTWARE.