<?php
namespace opensixt\BikiniTranslateBundle\Helpers;

/**
 * BikiniExport
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class BikiniExport
{

    protected $_version;

    protected $_xliffAttributes;
    protected $_xliffFileAttributes;
    protected $_xliffSourceAttributes;
    protected $_xliffTargetAttributes;

    protected $_sourceLanguage;
    protected $_targetLanguage;
    protected $_toolLanguage;


    /**
     * Constructor
     *
     * @param string $toolLanguage
     */
    public function __construct($toolLanguage)
    {
        $this->_toolLanguage = $toolLanguage;
    }

    /**
     * Set source language
     *
     * @param string $language
     */
    public function setSourceLanguage($language)
    {
        $this->_sourceLanguage = $language;
    }

    /**
     * Set target language
     *
     * @param string $language
     */
    public function setTargetLanguage($language)
    {
        $this->_targetLanguage = $language;
    }

    public function initXliff($version)
    {
        if (!empty($version)) {
            $this->_version = $version;

            switch ($version) {

            case 'human_translation_service':
                $this->_sourceLanguage = $this->_toolLanguage;
                $this->_xliffAttributes = array (
                    'version' => '1.1',
                    'xmlns' => 'urn:oasis:names:tc:xliff:document:1.1',
                );
                $this->_xliffFileAttributes = array (
                    'datatype' => 'xml/html',
                    'original' => 'PID-TRANSLATED',
                );
                $this->_xliffSourceAttributes = array (
                    'xml:lang' => str_replace('_', '-', $this->_sourceLanguage),
                );
                $this->_xliffTargetAttributes = array (
                    'xml:lang' => str_replace('_', '-', $this->_targetLanguage),
                );
                break;

            case '1.2':
            default:
                $this->_xliffAttributes = array (
                    'version' => '1.2',
                    'xmlns' => 'urn:oasis:names:tc:xliff:document:1.2',
                );
                $this->_xliffFileAttributes = array (
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
        if (!count($this->_xliffAttributes)) {
            throw new \Exception(__METHOD__ . ': _xliffAttributes is not set. Please set it with ' . __CLASS__ . '::initXliff() !');
        }
        if (!count($this->_xliffFileAttributes)) {
            throw new \Exception(__METHOD__ . ': _xliffFileAttributes is not set. Please set it with ' . __CLASS__ . '::initXliff() !');
        }

        $ret = '';
        if (is_array($data) && count($data)) {
            $dom = new \DOMDocument('1.0', "UTF-8");

            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;

            $xliff = $dom->createElement('xliff');
            foreach ($this->_xliffAttributes as $key => $value) {
                $attribute = $dom->createAttribute($key);
                $attribute->value = $value;
                $xliff->appendChild($attribute);
            }

            $file = $dom->createElement('file');
            foreach ($this->_xliffFileAttributes as $key => $value) {
                $attribute = $dom->createAttribute($key);
                $attribute->value = $value;
                $file->appendChild($attribute);
            }

            $body = $dom->createElement('body');

            foreach ($data as $elem) {
                $transUnit = $dom->createElement('trans-unit');
                $id = $dom->createAttribute('id');
                $id->value = $elem['hash'] . '_' . $elem['resource']['name'];
                $transUnit->appendChild($id);

                $source = $dom->createElement('source', $elem['source']);
                foreach ($this->_xliffSourceAttributes as $key => $value) {
                    $attribute = $dom->createAttribute($key);
                    $attribute->value = $value;
                    $source->appendChild($attribute);
                }

                $target = $dom->createElement('target', $elem['target'][0]['target']);
                foreach ($this->_xliffTargetAttributes as $key => $value) {
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
