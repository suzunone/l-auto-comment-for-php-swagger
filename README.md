# Auto-generate SwaggerPHP comments from Laravel.

## Description

Auto-generate [Swagger-PHP](https://github.com/zircote/swagger-php) comments from models.
Automatically generate [Swagger-PHP](https://github.com/zircote/swagger-php)  comments using routing, form requests, and small annotations.


## Installation
```shell
composer require --dev suzunone/l-auto-comment-for-php-swagger
```
```shell
php artisan vendor:publish --provider="AutoCommentForPHPSwagger\LAutoCommentForPHPSwaggerServiceProvider" --tag=config
```

## Usage
 * `openapi:info-comment`
   * Create OA\Info file
 * `openapi:security-comment`
   * Create Security Setting file
 * `openapi:create-model-to-schema`
   *  Create [Swagger-PHP](https://github.com/zircote/swagger-php)  schema file for models
 * `openapi:file-to-annotation`
   * Create a comment definition from yaml or json
 * `openapi:swagger-commen` 
   * Automatically generate comments for [Swagger-PHP](https://github.com/zircote/swagger-php) 

## openapi annotation
Small annotations can be used instead of the very large [Swagger-PHP](https://github.com/zircote/swagger-php)  format comments.

By writing in each method of Controller, [Swagger-PHP](https://github.com/zircote/swagger-php)  style annotations are automatically generated from FormRequest and routing.

### FormRequest
 * `@property {TYPE} {VARIABLE NAME} {DESCRIPTION}`
 * `@property-read {TYPE} {VARIABLE NAME} {DESCRIPTION}`

   Describes the parameters to be received in the request, the same as property in PHP Doc

 * `@openapi-in {REQUEST IN} {VARIABLE NAME}`
   Specify the parameter location for the in field.
   If it is not specified, in="query" will be given.

 * `@openapi-content {MIME} {FORMAT} {DESCRIPTION}`
   When using `request()->getContent()`, you can specify the contents of content.
   You can specify more than one, but only one description will be used.

### Controller
 * `@openapi`

   Describes if you want to include the `openapi:swagger-commen` target.

 * `@openapi-tags`

   Describe the API tag.

   Can be multiple. 

   If omitted, it will be automatically generated from the controller name, etc.

 * `@openapi-response {HTTP STATUS} {RESPONSE SCHEMA} {DESCRIPTION}`

    You can describe the response of the API.
    
    Specify a pre-prepared schemas.

    schemas can be nested.
    
    Multiple entries can be made by changing the HTTP STATUS

 * `@openapi-response-type {HTTP STATUS} {RESPONSE TYPE} {CONTENT TYPE}`

    The response type can be specified.
    You can choose from json xml media-type, and if you choose media-type, you can specify the content type.
    If there is more than one response type, multiple responses can be listed.
    If omitted, json will be used.
 
 * `@openapi-ignore-session-cookie`
 * `@openapi-session-cookie`

   Toggle whether to use session cookies.
 
 * `@openapi-ignore-csrf-header`
 * `@openapi-csrf-header`

   Toggle whether to use CSRF headers.

 * `@openapi-security {securityScheme} {SCOPES(comma-separated)}`
 * `@openapi-security {Security setting json}`

   Security settings to use




### A very simple example
```php
    /**
     * Here is the description of the API
     *
     * @openapi
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

# Schema pattern of auto-generated Model
## Create{ModelName}
Excluding columns with auto-increment.
Except for references to other models.

## Create{ModelName}WithRelation
Excluding columns with auto-increment.
Include references to other models.

## {ModelName}
Include auto-incrementing columns.
Except for references to other models.

## {ModelName}WithRelation
Include auto-incrementing columns.
Include references to other models.

## {ModelName}Many
Array of models.
Except for references to other models.

## {ModelName}WithRelationMany
Array of models.
Include references to other models.

## {ModelName}Paginate
Pagination of models.
Except for references to other models.

## {ModelName}WithRelationPaginate
Pagination of models.
Include references to other models.
