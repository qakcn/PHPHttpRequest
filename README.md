PHPHttpRequest
==============

PHPHttpRequest is a bunch of classes that are similar to JavaScript XMLHttpRequest.

Requirement
-----------

*   PHP > 5.3.0
    > * Fileinfo functions are not disabled
    > * Network functions are not disabled
    > * URLs functions are not disabled

Usage
-----

All codes are running in PHP.

### Common usage

You must `require` all files to use PHPHttpRequest, including `File.class.php`, `FormData.class.php`, `PHPHttpResponse.class.php` and `PHPHttpRequest.class.php`.

You can uncomment `namespace PHPHttpRequest;` at start of all files to use namespace, this can avoid conflict from other packages.

### File

1.  #### Create

    Create a **File** instance use file path.

        $file = new File('/path/to/file');

2.  #### Get file info

    Use properties `mimetype`, `filesize`, `filename` and `filepath` to get file MIME type, size, name and path.

    **Notice**: file that larger than 2GB may fail to get filesize.

        $file->mimetype;    //file MIME type
        $file->filesize;    //file size
        $file->filename;    //file name
        $file->filepath;    //file path

3.  #### Get file content

    File content can be get as string, can be sliced.

        $file->readAsString();      //get as string
        $file->readAsString(5);     //get as string from the 6th byte (slice off the starting 5 bytes)
        $file->readAsString(5, 30); //get as string from the 6th byte, and 30 bytes as most

    Or as [data URI](https://developer.mozilla.org/docs/Web/HTTP/data_URIs).

        $file->readAsDataURI();     //get as data URI

### FormData

FormData store data like HTML form.

1.  #### Create

    Create a **FormData** instance.

        $fd = new FormData();

2.  #### Add data

    Use `append` method to add **name** and **value** to data, those are similar to `name` and `value` attribute of HTML element `input`, `select`, `textarea`, etc.

        $fd->append('somename', 'somevalue');   //`name="somename"` and `value="somevalue"

    You can add **File** instance as **value**. You can override file name use a third parameter.

        $fd->append('file', $file);     //use File instance
        $fd->append('anotherfile', $file, 'bettername')     //override file name

    **Notice**: data won't be overwrite when using the same name. All of them will be stored.

3.  #### Multipart

    You can set property `multipart` to `true`, which can tell **PHPHttpRequest** to send **FormData** as MIME type `multipart/form-data`, or it will send with proper type (`application/x-www-form-urlencoded` when no File, or `multipart/form-data` when having File).

        $fd->multipart = true;      //force to use `multipart/form-data`

    You can get properties `multipart` and `hasFile` to check;

        $fd->multipart;     //default to `false`
        $fd->hasFile;       //`true` when having File

4.  #### Get data

    You can get data using `shift()` method from start, or using `pop()` method from end.

        $one = $fd->shift();    //from start
        $another = $fd->pop();  //from end

    You can `reset()` to get data again.

        $fd->reset();   //reset

### PHPHttpRequest

Use **PHPHttpRequest** is very similar to JavaScript XMLHttpRequest. Only difference is the way to get response data.

1.  #### Create

    Create a **PHPHttpRequest** instance.

        $phr = new PHPHttpRequest();

2.  #### Open a link

    You only need two parameters, **method** and **URL**.

        $phr->open('post', 'http://example.com/');

    **method** can be `head`, `get`, `post`, `put`, `delete`. More is WIP.

    **URL** should be absolute path with leading `http(s)://`. Not support basic authentication at present.

3.  #### Set HTTP headers

    You can set your own HTTP header, with **name** and **value** pair.

        $phr->setRequestHeader('Header-Name', 'Header-Value');

    **Notice**: header will be overwrite with the same **name**.
    **Notice**: `Content-Type` will be override when sending **FormData** or **File**. `Content-Length` will be override all the time. `Cookie` will be ignored all the time, use `setCookie()`.

4.  #### Set cookies

    You can set request cookies with **name** and **value**.

        $phr->setCookie('cookiename', 'cookievalue');

    **Notice**: cookie will be overwrite with the same **name**.

5.  #### Send data

    You can send data like this:

        $phr->send($data);

    `$data` can be **FormData** instance, **File** instance, string, or just nothing.

    **FormData** will be sent like HTML form.

    **File** will be sent originally as binary string, and set header `Content-Type` automatically.

    String will be sent originally, you can send every thing that can store in string.

    **Notice**: once sent, every parameter that set will be reset, you must set them again from step 2, or you won't send anything again.

6.  #### Get response data

    You can access response data using `response` property, which is a **PHPHttpResponse** instance. See more introductions below.

### PHPHttpResponse

1.  #### Create

    You don't need to create instance yourself. after successfully sent data using PHPHttpResquest, you can access like this:

        $phr->response;

2.  #### HTTP status code

    You can get status code using `status` property.

        $phr->response->status;

3.  #### Headers

    You can get HTTP headers using `getHeader()` method.

        $phr->response->getHeader();                //get all headers
        $phr->response->getHeader('Header-Name')    //get headers having the name

    An array will be returned, which contains at least one array, with `name` and `value` elements. `false` returned when no matching.

    **Notice**: `Set-Cookie` can not be get using this. Use `getCookie()` instead.

4.  #### Cookies

    You can get cookies using `getCookie()` method.

        $phr->response->getCookie();                //get all cookies
        $phr->response->getCookie('cookiename')    //get cookies having the name

    An array will be returned, which contains at least one array, with elements `name`, `value`, `expires`(in timestamp), `path`, `domain`, `secure` (boolean) or `httponly` (boolean). Not all elements exists.

5.  #### Resonse body

    You can get response body like this:

    $phr->response->data;

    This is original data. You must parse it yourself.

Donate
------

You can pay me some money to support my works. It's on your own.

* [PayPal](http://www.paypal.com): deng.chengzhi@gmail.com

* [Alipay](http://www.alipay.com): qakcn@hotmail.com
