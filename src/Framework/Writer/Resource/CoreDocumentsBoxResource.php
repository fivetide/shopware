<?php declare(strict_types=1);

namespace Shopware\Framework\Write\Resource;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Framework\Write\Field\IntField;
use Shopware\Framework\Write\Field\LongTextField;
use Shopware\Framework\Write\Field\StringField;
use Shopware\Framework\Write\Flag\Required;
use Shopware\Framework\Write\Resource;

class CoreDocumentsBoxResource extends Resource
{
    protected const DOCUMENTID_FIELD = 'documentID';
    protected const NAME_FIELD = 'name';
    protected const STYLE_FIELD = 'style';
    protected const VALUE_FIELD = 'value';

    public function __construct()
    {
        parent::__construct('s_core_documents_box');

        $this->fields[self::DOCUMENTID_FIELD] = (new IntField('documentID'))->setFlags(new Required());
        $this->fields[self::NAME_FIELD] = (new StringField('name'))->setFlags(new Required());
        $this->fields[self::STYLE_FIELD] = (new LongTextField('style'))->setFlags(new Required());
        $this->fields[self::VALUE_FIELD] = (new LongTextField('value'))->setFlags(new Required());
    }

    public function getWriteOrder(): array
    {
        return [
            \Shopware\Framework\Write\Resource\CoreDocumentsBoxResource::class,
        ];
    }

    public static function createWrittenEvent(array $updates, TranslationContext $context, array $errors = []): ?\Shopware\Framework\Event\CoreDocumentsBoxWrittenEvent
    {
        if (empty($updates) || !array_key_exists(self::class, $updates)) {
            return null;
        }

        $event = new \Shopware\Framework\Event\CoreDocumentsBoxWrittenEvent($updates[self::class] ?? [], $context, $errors);

        unset($updates[self::class]);

        $event->addEvent(\Shopware\Framework\Write\Resource\CoreDocumentsBoxResource::createWrittenEvent($updates, $context));

        return $event;
    }
}
