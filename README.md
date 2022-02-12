# Auto-generate PHPSwagger comments from Laravel.

## Description

Auto-generate PHPSwagger comments from models.
Automatically generate PHPSwagger comments using routing, form requests, and small annotations.


## Installation
```shell
composer require --dev suzunone/auto-comment-for-l5-swagger
```
```shell
php artisan vendor:publish --provider="AutoCommentForL5Swagger\AutoCommentForL5SwaggerServiceProvider" --tag=config
```

## Usage
 * `openapi:create-model-to-schema`
 * `openapi:file-to-annotation`
 * `openapi:l5-swagger-comment` 

## openapi annotation
Small annotations can be used instead of the very large PHPSwagger format comments.

By writing in each method of Controller, PHPSwagger style annotations are automatically generated from FormRequest and routing.


 * `@openapi`

   Describes if you want to include the `openapi:l5-swagger-comment` target.

 * `@openapi-tags`

   Describe the API tag.

   Can be multiple. 

   If omitted, it will be automatically generated from the controller name, etc.

 * `@openapi-response {HTTP STATUS} {RESPONSE SCHEMA} {DESCRIPTION}`

    You can describe the response of the API.
    
    Specify a pre-prepared schemas.

    schemas can be nested.
    
    Multiple entries can be made by changing the HTTP STATUS

 * `@openapi-ignore-session-cookie`

   Describes when session cookies are not used.
 
 * `@openapi-ignore-csrf-cookie`

   Describes when CSRF cookies are not used.


### A very simple example
```php
    /**
     * Here is the description of the API
     *
     * @openapi
     * @openapi-ignore-session-cookie
     * @openapi-ignore-csrf-cookie
     * @openapi-tags TAG-A
     * @openapi-tags TAG-B
     * @openapi-response 200 #/components/schemas/ExampleModel Success
     * @openapi-response 400 #/components/schemas/Error Bad Request
     * @openapi-response 403 #/components/schemas/Error Forbidden
     * @openapi-response 404 #/components/schemas/Error Not Found
     * @openapi-response 405 #/components/schemas/Error Invalid Input
     */
```


```php
/**
 * @OA\Get(
 *   tags={"TAG-A", "TAG-B"},
 *   operationId="exampleIndex",
 *   path="/api/example",
 *   description="Here is the description of the API",
 *
 *   @OA\Response(
 *     response="200",
 *     description="Success",
 *     @OA\JsonContent(ref="#/components/schemas/ExampleModel")
 *   ),
 *   @OA\Response(
 *     response="400",
 *     description="Bad Request",
 *     @OA\JsonContent(ref="#/components/schemas/CommonError")
 *   ),
 *   @OA\Response(
 *     response="403",
 *     description="Forbidden",
 *     @OA\JsonContent(ref="#/components/schemas/CommonError")
 *   ),
 *   @OA\Response(
 *     response="404",
 *     description="Not Found",
 *     @OA\JsonContent(ref="#/components/schemas/CommonError")
 *   ),
 *   @OA\Response(
 *     response="405",
 *     description="Invalid Input",
 *     @OA\JsonContent(ref="#/components/schemas/CommonError")
 *   ),
 * )
*/
```

## Nest Response Usage

### Single Nest
```
@openapi-response 200 #/components/schemas/ExampleModel&foo_model=#/components/schemas/FooModel&bar_mode=#/components/schemas/BarModel
```

### DeepNest
```
@openapi-response 200 #/components/schemas/ExampleModel&foo_model[#/components/schemas/FooModel]&foo_model[bar_mode]=#/components/schemas/BarModel
```
