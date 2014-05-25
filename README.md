FloppyServer
============

FloppyServer is a file storage library. There is also FloppyClient library and symfony2 bundle that adds few integration 
points to symfony2 applications.

When your application uses images and other files intensively, there is a **problem** with generation required **thumbnails**,
code to handle file **upload is full of boilerplate**, dealing with files is awkward. There are very good bundles to Imagine
that partially resolves first problem, but other problems not. This documentation covers only FloppyServer library, if
you want to see how to use FloppyServer from the client side, check FloppyBundle and FloppyClient libraries.

FloppyServer is able to do some extra processing before store file into storage and before send file to client, for 
example: 

* before sending file to client: thumbnail of image with requested dimensions can be created
* before storing: file optimizations, now there is implemented resizing very large images but in very near feature there
will be few nice optimizations

FloppyServer is designed to **handle multiple clients**, so you can setup **one instance** of FloppyServer and use it in **many
applications**. This library is fully customizable and extensible, you can define what file types can be stored, what file 
types would be processed before sending to client (for example generating thumbnails, adding watermarks etc), what file
types would and how to be optimized, what security credentials would be required to upload / download files and more.

CORS / crossdomain.xml / clientaccesspolicy.xml are supported by adding simple entry into configuration so you can upload
to FloppyServer directly from web browser even if FloppyServer instance is running on different host than your app. There 
is also nice symfony2 integration (see FloppyBundle documentation), so uploading files and using files in application is 
very simple and elegant.

![Architecture][1]

Simple setup example
====================

To create your floppy server application you should create **empty composer project**, add dependency to floppy/server 
package and create following index.php file.

composer.json file:

```
    "require": {
        "floppy/server": "*"
    }
```

web/index.php file:

```php

    require_once __DIR__.'/../vendor/autoload.php';
    
    $requestHandlerFactory = new \Floppy\Server\RequestHandler\RequestHandlerFactory();
    
    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        'storage.dir' => __DIR__.'/../storage',
        'secretKey' => 'super-secret-key',
        'action.cors.allowedOriginHosts' => array(
            '*.your-client-host.com',
        ),
    ));
    
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    
    $response = $requestHandler->handle($request);
    
    $response->send();

```

Virtual host should be created in web directory, storage will be in web/../storage dir.

Detailed configuration and extension points
======================

Security
--------

There are two levels to define security rules:

* **firewall** - deny or allow to execute certain action, you can restrict for example access to upload action to certain ip
address. By default there are no firewalls so everyone is able to upload files.
* **security rules** - more specific and customizable security rules, you can take decision to grand or deny access to action
having information about accessed file, extra information provided by client etc.

To define firewall for given action, use this code:

```php

    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        //other options...
        'requestHandler.firewall.download' => function($container){ //it can be also requestHandler.firewall.upload
            return function(\Symfony\Component\HttpFoundation\Request $request){
                if(/* if access should be denied */) {
                    throw new \Floppy\Server\RequestHandler\Exception\AccessDeniedException();
                }
            };
        },
        //...
    ));

```

FloppyServer uses **Pimple2** library as dependency injection container, so $container variable in Closure param is Pimple
instance.

You can also use security rules to have access to information about file that is being uploaded or downloaded while taking
decision about permissions:

```php

    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        //other options...
            'action.upload.securityRule' => function($container){ //it can be also action.download.securityRule
                return //implementation of Floppy\Server\RequestHandler\Security\Rule interface
            },
        //...
    ));
```

`Floppy\Server\RequestHandler\Security\Rule` interface has one method: `checkRule(Request $request, $object = null)`.
Object can be a Floppy\Common\FileSource when action is upload, Floppy\Common\FileId when action is download. Current 
only implementation of this interface is `PolicyRule` that is able to check expiration of request and type of uploaded 
file. `PolicyRule` checks two parameters from `Request`: `policy` and `signature`. `Policy` contains information about 
request expiration time and supported file types of the request, `signature` ensures policy was send by approved client 
and was not modified by third-party entity.

PolicyRule configuration:

```php

    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        //other options...
            'action.upload.securityRule' => function($container){ //it can be also action.download.securityRule
                return new \Floppy\Server\RequestHandler\Security\PolicyRule($container['checksumChecker'], $container['fileHandlers']),
            },
        //...
    ));
```

When you **define PolicyRule to upload** (or/and download) action, **every upload** (or/and download) request should contains 
**security credentials**. To generate proper url to file from **FloppyClient**, you should provide security credentials in third
argument of `Floppy\Client\UrlGenerator`::`generate($fileId, $fileType = null, $credentialAttributes = array())` method,
example:

```php

    $url = $urlGenerator->generate(new \Floppy\Common\FileId('someid.jpg'), 'image', array('expiration' => time() + 60));

```

To attach security credentials to upload action using Floppy\Client\FloppyClient class you should use second argument of
`FloppyClient`::`upload` method:

```php

    $flieId = $client->upload($someFileSource, array('expiration' => time() + 60, 'file_types' => array('image')));
    
```

If you use **FloppyBundle** you have possibility to provide security credentials in `floppy_file` form type:

```php

    //for example in controller
    $form = $this->createFormBuilder($document)
        ->add('file', 'floppy_file', array('credentials' => array('expiration' => time()+500, 'file_types' => array('image', 'file'))))
        ->getForm();

```

You can also provide credentials while generating url from floppy_url twig function:

```

    <img src="{{ floppy_url(document.file, { "width": 100, "height": 100 }, { 
        "expiration": date().timestamp + 60, "some-custom-attr": "custom-value" 
    }) }}" />

```

If you want to add extra security attributes, you can extend PolicyRule and add support for your extra attributes, for 
example user id etc.


File handlers
-------------

There are two **file groups** by default: images and other files. Images has its own `FileHandler` that adds support for
processing before storing and sending file to a client. If you want to create your own files' group you should create
`Floppy\Server\FileHandler\FileHandler` implementation (`Floppy\Server\FileHandler\AbstractFileHandler` implements the most
part of file handler).

By default file will be processed by `image` file handler when file has jpg, jpeg, png or gif extension **and** has
image/png, image/jpg, image/jpeg, image/pjpeg or image/gif mime type. `file` file handler is **turned off by default**, so 
you should **explicitly configure** what extensions and mime-types are supported. Extensions and mime types can be configured
by options: `fileHandlers.image.mimeTypes`, `fileHandlers.image.extensions`, `fileHandlers.file.mimeTypes`, 
`fileHandlers.file.extensions`. Example:

```php

    //add support for text files - both mimeTypes and extensions must be configured
    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        'fileHandlers.file.mimeTypes' => array('text/plain'),
        'fileHandlers.file.extensions' => array('txt'),
    ));

```

There is an example how to configure your own file handler:

```php

    //add support for text files - both mimeTypes and extensions must be configured
    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        'fileHandlers' => function($container){
            return array(
                //default "image" file handler
                $container['fileHandlers.image.name'] => $container['fileHandlers.image'],
                
                'custom' => new YourCustomFileHandler(new CustomPathMatcher(), array()),
                
                //default "file" file handler
                $container['fileHandlers.file.name'] => $container['fileHandlers.file'],
            );
        }
    ));

```

Recommended setups
==================

FloppyServer can be setup in few ways, there are 3 recommended setups. **Before reading this section** read "Simple setup 
example" section, because it says how composer.json and index.php files should to be.

Public directory + apache htaccess (or nginx replacement) fallback for unexisting files
----------------

The idea is to put your storage directory to **public directory** and **configure htaccess** to fallback requests to unexisting 
files to your index.php file. This is the most efficient way to setup Floppy. When requested thumbnail exists (because
it was generated in the past) server directly send file **without running php code**. When thumbnail is missing, it will
be created by FloppyServer.

Advantages:

* performance
* easy to setup

Disadvantages:

* uploaded and generated files are **publicly visible** (but it names are difficult to guess), even if you **configured
security credentials** to access to files - so it is security issue

Example:

```php

    //part of web/index.php file in public directory

    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        'storage.dir' => __DIR__,//path to public directory
        'secretKey' => 'super-secret-key',
        'action.cors.allowedOriginHosts' => array(
            '*.your-client-host.com',
        ),
    ));
    
    //example of minimal web/.htaccess file
    
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule (.*) index.php/$1

```

Non-public directory + apache x-sendfile (or nginx replacement)
----------------

The alternative is to setup FloppyServer in **non-public directory** and adds **x-sendfile** (or nginx replacement) support for
gain performance.

Advantages:

* **more secure** than "public directory" solution, **every request runs php code** so eventual security checks are done always

Disadvantages:

* performance is worst than in "public directory" solution, every request runs php code ;) But x-sendfile improves
performance
* you should have configured x-sendfile module (or nginx replacement) on your server

Example: 

```php

    //part of web/index.php file in public directory

    $requestHandler = $requestHandlerFactory->createRequestHandler(array(
        'storage.dir' => __DIR__.'/../storage,//path to private directory
        'secretKey' => 'super-secret-key',
        'action.cors.allowedOriginHosts' => array(
            '*.your-client-host.com',
        ),
        'action.download.responseFactory' => new \Floppy\Server\RequestHandler\XSendFileDownloadResponseFactory(),
    ));
    
    //web/.htaccess file
    
    <IfModule mod_xsendfile.c>
        <files index.php>
            XSendFile on
        </files>
    </IfModule>
    
    //your virtual host configuration
    
    <VirtualHost ...>
        XSendFilePath /absolute/path/to/your/storage/directory
    </VirtualHost>

```

More about x-sendfile configuration you can read in [documentation][2]

If you have no x-sendfile mod on your webserver, you should use configuration from "Simple setup example" section. It will
be working but would be inefficient.


Bundle FloppyServer into your symfony application
-------------

Not yet available ;)

License
=======

This project is under **MIT** license.

[1]: doc/Resources/FloppyServer.png
[2]: https://tn123.org/mod_xsendfile/
