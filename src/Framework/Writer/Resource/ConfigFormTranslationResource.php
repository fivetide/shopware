<?php declare(strict_types=1);

namespace Shopware\Framework\Write\Resource;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Write\Field\FkField;
use Shopware\Framework\Write\Field\LongTextField;
use Shopware\Framework\Write\Field\ReferenceField;
use Shopware\Framework\Write\Field\StringField;
use Shopware\Framework\Write\Field\UuidField;
use Shopware\Framework\Write\Flag\Required;
use Shopware\Framework\Write\Resource;

class ConfigFormTranslationResource extends Resource
{
    protected const UUID_FIELD = 'uuid';
    protected const LABEL_FIELD = 'label';
    protected const DESCRIPTION_FIELD = 'description';

    public function __construct()
    {
        parent::__construct('config_form_translation');

        $this->primaryKeyFields[self::UUID_FIELD] = (new UuidField('uuid'))->setFlags(new Required());
        $this->fields[self::LABEL_FIELD] = new StringField('label');
        $this->fields[self::DESCRIPTION_FIELD] = new LongTextField('description');
        $this->fields['configForm'] = new ReferenceField('configFormUuid', 'uuid', \Shopware\Framework\Write\Resource\ConfigFormResource::class);
        $this->fields['configFormUuid'] = (new FkField('config_form_uuid', \Shopware\Framework\Write\Resource\ConfigFormResource::class, 'uuid'))->setFlags(new Required());
        $this->fields['locale'] = new ReferenceField('localeUuid', 'uuid', \Shopware\Locale\Writer\Resource\LocaleResource::class);
        $this->fields['localeUuid'] = (new FkField('locale_uuid', \Shopware\Locale\Writer\Resource\LocaleResource::class, 'uuid'))->setFlags(new Required());
    }

    public function getWriteOrder(): array
    {
        return [
            \Shopware\Framework\Write\Resource\ConfigFormResource::class,
            \Shopware\Locale\Writer\Resource\LocaleResource::class,
            \Shopware\Framework\Write\Resource\ConfigFormTranslationResource::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $errors = []): ?\Shopware\Framework\Event\ConfigFormTranslationWrittenEvent
    {
        if (empty($updates) || !array_key_exists(self::class, $updates)) {
            return null;
        }

        $event = new \Shopware\Framework\Event\ConfigFormTranslationWrittenEvent($updates[self::class] ?? [], $context, $errors);

        unset($updates[self::class]);

        $event->addEvent(\Shopware\Framework\Write\Resource\ConfigFormResource::createWrittenEvent($updates, $context));
        $event->addEvent(\Shopware\Locale\Writer\Resource\LocaleResource::createWrittenEvent($updates, $context));
        $event->addEvent(\Shopware\Framework\Write\Resource\ConfigFormTranslationResource::createWrittenEvent($updates, $context));

        return $event;
    }
}
