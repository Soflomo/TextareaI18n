<?php

return array(
    'soflomo_textarea' => array(
        'text_entity_class' => 'Soflomo\TextareaI18n\Entity\Text',
    ),

    'doctrine' => array(
        'driver' => array(
            'soflomo_textarea_i18n' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => __DIR__ . '/mapping'
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Soflomo\TextareaI18n\Entity' => 'soflomo_textarea_i18n',
                ),
            ),
        ),
        'entity_resolver' => array(
            'orm_default' => array(
                'resolvers' => array(
                    'Soflomo\Textarea\Entity\TextInterface'                => 'Soflomo\TextareaI18n\Entity\Text',
                    'Soflomo\TextareaI18n\Entity\TextTranslationInterface' => 'Soflomo\TextareaI18n\Entity\TextTranslation',
                ),
            ),
        ),
    ),
);