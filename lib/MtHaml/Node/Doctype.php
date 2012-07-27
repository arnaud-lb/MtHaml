<?php

namespace MtHaml\Node;

use MtHaml\NodeVisitor\NodeVisitorInterface;

/**
 * Doctype Node
 */
class Doctype extends NodeAbstract
{
    protected $doctypeId;
    protected $options;

    protected $doctypes = array(
        'xhtml' => array(
            '' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'Strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
            'Frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
            '5' => '<!DOCTYPE html>',
            '1.1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
            'Basic' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
            'Mobile' => '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">',
            'RDFa' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
        ),
        'html4' => array(
            '' => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            'Strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            'Frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
        ),
        'html5' => array(
            '' => '<!DOCTYPE html>',
        ),
    );

    public function __construct(array $position, $doctypeId, $options)
    {
        parent::__construct($position);
        $this->doctypeId = $doctypeId;
        $this->options = $options;
    }

    public function getDoctype($format)
    {
        if ($this->doctypeId === 'XML') {
            if (!empty($this->options)) {
                return sprintf("<?xml version='1.0' encoding='%s' ?>", $this->options);
            } else {
                return "<?xml version='1.0' encoding='utf-8' ?>";
            }
        }

        if (empty($this->doctypeId)) {
            return $this->doctypes[$format][''];
        } else if (isset($this->doctypes[$format][$this->doctypeId])) {
            return $this->doctypes[$format][$this->doctypeId];
        } else {

            $doctypes = array();
            foreach ($this->doctypes[$format] as $key => $doctype) {
                $doctypes[] = "'".trim('!!! ' . $key)."'";
            }

            trigger_error(sprintf("No such doctype '!!! %s' for the format '%s'. Available doctypes for the current format are: %s", $this->doctypeId, $format, implode(', ', $doctypes)), E_USER_WARNING);

            return $this->doctypes[$format][''];
        }
    }

    public function getDoctypeId()
    {
        return $this->doctypeId;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getNodeName()
    {
        return 'doctype';
    }

    public function accept(NodeVisitorInterface $visitor)
    {
        $visitor->enterDoctype($this);
        $visitor->leaveDoctype($this);
    }
}

