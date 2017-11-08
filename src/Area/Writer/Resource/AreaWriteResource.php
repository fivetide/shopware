<?php declare(strict_types=1);

namespace Shopware\Area\Writer\Resource;

use Shopware\Api\Write\Field\BoolField;
use Shopware\Api\Write\Field\SubresourceField;
use Shopware\Api\Write\Field\TranslatedField;
use Shopware\Api\Write\Field\UuidField;
use Shopware\Api\Write\Flag\Required;
use Shopware\Api\Write\WriteResource;
use Shopware\Area\Event\AreaWrittenEvent;
use Shopware\AreaCountry\Writer\Resource\AreaCountryWriteResource;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Shop\Writer\Resource\ShopWriteResource;
use Shopware\TaxAreaRule\Writer\Resource\TaxAreaRuleWriteResource;

class AreaWriteResource extends WriteResource
{
    protected const UUID_FIELD = 'uuid';
    protected const ACTIVE_FIELD = 'active';
    protected const NAME_FIELD = 'name';

    public function __construct()
    {
        parent::__construct('area');

        $this->primaryKeyFields[self::UUID_FIELD] = (new UuidField('uuid'))->setFlags(new Required());
        $this->fields[self::ACTIVE_FIELD] = new BoolField('active');
        $this->fields[self::NAME_FIELD] = new TranslatedField('name', ShopWriteResource::class, 'uuid');
        $this->fields['translations'] = (new SubresourceField(AreaTranslationWriteResource::class, 'languageUuid'))->setFlags(new Required());
        $this->fields['countries'] = new SubresourceField(AreaCountryWriteResource::class);
        $this->fields['taxAreaRules'] = new SubresourceField(TaxAreaRuleWriteResource::class);
    }

    public function getWriteOrder(): array
    {
        return [
            self::class,
            AreaTranslationWriteResource::class,
            AreaCountryWriteResource::class,
            TaxAreaRuleWriteResource::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $rawData = [], array $errors = []): AreaWrittenEvent
    {
        $uuids = [];
        if (isset($updates[self::class])) {
            $uuids = array_column($updates[self::class], 'uuid');
        }

        $event = new AreaWrittenEvent($uuids, $context, $rawData, $errors);

        unset($updates[self::class]);

        /**
         * @var WriteResource
         * @var string[]      $identifiers
         */
        foreach ($updates as $class => $identifiers) {
            if (!array_key_exists($class, $updates) || count($updates[$class]) === 0) {
                continue;
            }

            $event->addEvent($class::createWrittenEvent($updates, $context));
        }

        return $event;
    }
}
