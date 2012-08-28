<?php
namespace opensixt\BikiniTranslateBundle\Helpers;

/**
 * BikiniExport
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class BikiniExport
{
    protected $version;

    protected $xliffAttributes;
    protected $xliffFileAttributes;
    protected $xliffSourceAttributes;
    protected $xliffTargetAttributes;

    protected $sourceLanguage;
    protected $targetLanguage;
    protected $toolLanguage;

    /**
     * Constructor
     *
     * @param string $toolLanguage
     */
    public function __construct($toolLanguage)
    {
        $this->toolLanguage = $toolLanguage;
    }

    /**
     * Set source language
     *
     * @param string $language
     */
    public function setSourceLanguage($language)
    {
        $this->sourceLanguage = $language;
    }

    /**
     * Set target language
     *
     * @param string $language
     */
    public function setTargetLanguage($language)
    {
        $this->targetLanguage = $language;
    }

    public function initXliff($version)
    {
        if (!empty($version)) {
            $this->version = $version;

            switch ($version) {
                case 'human_translation_service':
                    $this->sourceLanguage = $this->toolLanguage;
                    $this->xliffAttributes = array (
                        'version' => '1.1',
                        'xmlns' => 'urn:oasis:names:tc:xliff:document:1.1',
                    );
                    $this->xliffFileAttributes = array (
                        'datatype' => 'xml/html',
                        'original' => 'PID-TRANSLATED',
                    );
                    $this->xliffSourceAttributes = array (
                        'xml:lang' => str_replace('_', '-', $this->sourceLanguage),
                    );
                    $this->xliffTargetAttributes = array (
                        'xml:lang' => str_replace('_', '-', $this->targetLanguage),
                    );
                    break;
                case '1.2':
                default:
                    $this->xliffAttributes = array (
                        'version' => '1.2',
                        'xmlns' => 'urn:oasis:names:tc:xliff:document:1.2',
                    );
                    $this->xliffFileAttributes = array (
                        'source-language' => 'en',
                        'datatype' => 'plaintext',
                        'original' => 'file.ext',
                    );
                    break;
            }
        }
    }

    /**
     * Generate Xliff
     *
     * @param type $data
     */
    public function getDataAsXliff($data)
    {
        // Exceptions
        if (!count($this->xliffAttributes)) {
            throw new \Exception(
                __METHOD__ . ': xliffAttributes is not set. Please set it with ' . __CLASS__ . '::initXliff() !'
            );
        }
        if (!count($this->xliffFileAttributes)) {
            throw new \Exception(
                __METHOD__ . ': xliffFileAttributes is not set. Please set it with ' . __CLASS__ . '::initXliff() !'
            );
        }

        $ret = '';
        if (count($data)) {
            $dom = new \DOMDocument('1.0', "UTF-8");

            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            $xliff = $dom->createElement('xliff');
            foreach ($this->xliffAttributes as $key => $value) {
                $attribute = $dom->createAttribute($key);
                $attribute->value = $value;
                $xliff->appendChild($attribute);
            }

            $file = $dom->createElement('file');
            foreach ($this->xliffFileAttributes as $key => $value) {
                $attribute = $dom->createAttribute($key);
                $attribute->value = $value;
                $file->appendChild($attribute);
            }

            $body = $dom->createElement('body');

            foreach ($data as $elem) {
                $transUnit = $dom->createElement('trans-unit');
                $id = $dom->createAttribute('id');
                $id->value = $elem->getHash() . '_' . $elem->getResource()->getName();
                $transUnit->appendChild($id);

                $source = $dom->createElement('source', $elem->getSource());
                foreach ($this->xliffSourceAttributes as $key => $value) {
                    $attribute = $dom->createAttribute($key);
                    $attribute->value = $value;
                    $source->appendChild($attribute);
                }

                // if target is not set, return source
                $targetObject = $elem->getCurrentTarget();
                if (empty($targetObject)) {
                    $targetValue = $elem->getSource();
                } else {
                    $targetValue = $targetObject->getTarget();
                }

                $target = $dom->createElement('target', $targetValue);
                foreach ($this->xliffTargetAttributes as $key => $value) {
                    $attribute = $dom->createAttribute($key);
                    $attribute->value = $value;
                    $target->appendChild($attribute);
                }

                $transUnit->appendChild($source);
                $transUnit->appendChild($target);

                $body->appendChild($transUnit);
            }

            $file->appendChild($body);
            $xliff->appendChild($file);
            $dom->appendChild($xliff);

            $ret = $dom->saveXML();
        }

        return $ret;
    }
}

