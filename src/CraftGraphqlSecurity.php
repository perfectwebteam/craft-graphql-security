<?php
/**
 * Craft GraphQL Security plugin for Craft CMS 3.x
 *
 * An assortment of security measures when using Craft CMS with GraphQL
 *
 * @link      https://perfectwebteam.nl/
 * @copyright Copyright (c) 2022 Perfectwebteam
 */

namespace perfectwebteam\craftgraphqlsecurity;


use craft\base\Plugin;
use craft\events\DefineGqlValidationRulesEvent;
use craft\services\Gql;
use craft\web\Request;
use GraphQL\Error\Error;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Validator\ValidationContext;
use yii\base\Event;

/**
 * Class CraftGraphqlSecurity
 *
 * @author    Perfectwebteam
 * @package   CraftGraphqlSecurity
 * @since     0.0.1
 *
 */
class CraftGraphqlSecurity extends Plugin
{
    /**
     * @var CraftGraphqlSecurity
     */
    public static $plugin;

    /**
     * @var string
     */
    public string $schemaVersion = '0.0.1';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Gql::class,
            Gql::EVENT_DEFINE_GQL_VALIDATION_RULES,
            static function(DefineGqlValidationRulesEvent $event) {
                $event->validationRules[self::class] = self::$plugin;
            }
        );
    }

    public function getVisitor(ValidationContext $context)
    {
        return [
            NodeKind::OPERATION_DEFINITION => static function (OperationDefinitionNode $node) use ($context) {
                $req = new Request();
                if ($node->operation === 'mutation' && $req->isGet) {
                    $context->reportError(new Error(
                        'operation not permitted'
                    ));
                }
            },
        ];
    }
}
