<?php

namespace ArieTimmerman\Laravel\SCIMServer;

use ArieTimmerman\Laravel\SCIMServer\Attribute\AttributeMapping;
use ArieTimmerman\Laravel\SCIMServer\SCIM\Schema;

class SCIMConfig
{
    public function getConfigForResource($name)
    {
        if ($name == 'Users') {
            return $this->getUserConfig();
        } else {
            $result = $this->getConfig();
            return @$result[$name];
        }
    }

    public function getUserConfig()
    {
        return [

            // Set to 'null' to make use of auth.providers.users.model (App\User::class)
            'class' => Helper::getAuthUserClass(),

            'validations' => [

                'urn:ietf:params:scim:schemas:core:2.0:User:userName' => 'required',
                'urn:ietf:params:scim:schemas:core:2.0:User:password' => 'nullable',
                'urn:ietf:params:scim:schemas:core:2.0:User:active' => 'boolean',
                'urn:ietf:params:scim:schemas:core:2.0:User:emails' => 'required|array',
                'urn:ietf:params:scim:schemas:core:2.0:User:emails.*.value' => 'required|email',
                'urn:ietf:params:scim:schemas:core:2.0:User:roles' => 'nullable|array',
                'urn:ietf:params:scim:schemas:core:2.0:User:roles.*.value' => 'required',

            ],

            'singular' => 'User',
            'schema' => [Schema::SCHEMA_USER],

            //eager loading
            'withRelations' => [],
            'map_unmapped' => true,
            'unmapped_namespace' => 'urn:ietf:params:scim:schemas:laravel:unmapped',
            'description' => 'User Account',

            // Map a SCIM attribute to an attribute of the object.
            'mapping' => [

                'id' => AttributeMapping::eloquent("id")->disableWrite(),

                'externalId' => null,

                'meta' => [
                    'created' => AttributeMapping::eloquent("created_at")->disableWrite(),
                    'lastModified' => AttributeMapping::eloquent("updated_at")->disableWrite(),

                    'location' => (new AttributeMapping())->setRead(function ($object) {
                        return route('scim.resource', [
                            'name' => 'Users',
                            'id' => $object->id
                        ]);
                    })->disableWrite(),

                    'resourceType' => AttributeMapping::constant("User")
                ],

                'schemas' => AttributeMapping::constant([
                    'urn:ietf:params:scim:schemas:core:2.0:User',
                    'example:name:space',
                ])->ignoreWrite(),

                'example:name:space' => [
                    'cityPrefix' => AttributeMapping::eloquent('cityPrefix')
                ],

                'urn:ietf:params:scim:schemas:core:2.0:User' => [

                    'userName' => null,

                    'name' => [
                        'formatted' => AttributeMapping::eloquent("full_name"),
                        'familyName' => AttributeMapping::eloquent("last_name"),
                        'givenName' => AttributeMapping::eloquent("first_name"),
                        'middleName' => null,
                        'honorificPrefix' => null,
                        'honorificSuffix' => null
                    ],

                    'displayName' => null,
                    'nickName' => null,
                    'profileUrl' => null,
                    'title' => AttributeMapping::eloquent("job_title"),
                    'userType' => null,
                    'preferredLanguage' => null, // Section 5.3.5 of [RFC7231]
                    'locale' => null, // see RFC5646
                    'timezone' => AttributeMapping::eloquent("timezone"), // see RFC6557
                    'active' => AttributeMapping::eloquent("user_active"),

                    'password' => AttributeMapping::eloquent('password')->disableRead(),

                    // Multi-Valued Attributes
                    'emails' => [[
                        "value" => AttributeMapping::eloquent("email"),
                        "display" => null,
                        "type" => AttributeMapping::constant("work")->ignoreWrite(),
                        "primary" => AttributeMapping::constant(true)->ignoreWrite()
                    ]],
                    'roles' => AttributeMapping::eloquent('roles'),
                    'x509Certificates' => null
                ],

            ]
        ];
    }

    public function getConfig()
    {
        return [

            'Users' => $this->getUserConfig()
        ]

            ;
    }
}
