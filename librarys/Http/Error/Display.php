<?php

    namespace Librarys\Http\Error;

    use Librarys\Http\Buffer;

    class Display
    {

        private static $errors;
        private static $exceptions;
        private static $isDisplayAlready;

        public static function putError($label, $type, $str, $file, $line)
        {
            self::putErrors([
                'label' => $label,
                'type'  => $type,
                'str'   => $str,
                'file'  => $file,
                'line'  => $line
            ]);
        }

        public static function putErrors($errors)
        {
            self::$errors = $errors;
        }

        public static function putExceptions($exceptions)
        {
            self::$exceptions = $exceptions;
        }

        public static function isErrors()
        {
            return is_array(self::$errors) && count(self::$errors) > 0;
        }

        public static function isExceptions()
        {
            return is_object(self::$exceptions);
        }

        public static function display()
        {
            if (self::$isDisplayAlready == false)
                self::$isDisplayAlready = true;
            else
                return;

            $isErrors     = self::isErrors();
            $isExceptions = self::isExceptions();

            if ($isErrors == false && $isExceptions == false)
                return;

            Buffer::clearBuffer();

            $class      = null;
            $trace      = null;
            $message    = null;
            $file       = null;
            $line       = 0;
            $label      = null;

            if ($isExceptions) {
                $class   = get_class(self::$exceptions);
                $trace   = self::$exceptions->getTrace();
                $message = self::$exceptions->getMessage();
                $file    = self::$exceptions->getFile();
                $line    = self::$exceptions->getLine();
                $label   = Handler::getLabelErrorCode(self::$exceptions->getCode());
            } else {
                $label = Handler::getLabelErrorCode(self::$errors['type']);
                $message = self::$errors['str'];
                $file    = self::$errors['file'];
                $line    = self::$errors['line'];
            }

            $object = new \stdClass();

            $object->isErrors     = $isErrors;
            $object->isExceptions = $isExceptions;
            $object->class        = $class;
            $object->trace        = $trace;
            $object->message      = $message;
            $object->line         = $line;
            $object->file         = $file;
            $object->label        = $label;

            unset($isErrors);
            unset($isExceptions);
            unset($class);
            unset($trace);
            unset($message);
            unset($file);
            unset($label);

            echo '<!DOCTYPE html>';
            echo '<html>';
                echo '<head>';

                    if ($object->isErrors)
                        echo '<title>' . $object->label . '</title>';
                    else
                        echo '<title>' . $object->message . '</title>';

                    echo '<meta http-equiv="Content-Type" content="text/html; charset=uft-8"/>';
                    echo '<meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">';
                    echo '<meta http-equiv="Cache-Control" content="private, max-age=0, no-cache, no-store, must-revalidate"/>';
                    echo '<meta http-equiv="Pragma" content="no-cache"/>';
                    echo '<meta name="robots" content="noindex, nofollow, noodp, nodir"/>';
                    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=false"/>';

                    echo '<style type="text/css">';
                        echo 'body { font-family: Verdana, Geneva, sans-serif; font-size: 15px; background-color: #e0e0e0; margin: 0; padding: 50px; }';
                        echo 'h2 { margin: 0; padding: 0; font-weight: normal; }';
                        echo 'div.container { background-color: #ffffff; box-shadow: 1px 1px 3px rgba(0, 0, 0, 0.05); }';
                        echo 'div.error { color: #707070; background-color: #ffffff; padding: 20px; word-wrap: break-word; }';
                        echo 'ul.list, ul.list li { list-style: decimal; margin-top: 0; margin-bottom: 0; }';
                        echo 'ul.list li { color: #505050; margin-left: 0; padding: 8px; padding-left: 0; padding-right: 15px; word-wrap: break-word; }';
                        echo 'span.file { color: #808080; }';
                        echo 'span.class, span.resource { text-decoration: underline; }';
                    echo '</style>';
                echo '</head>';
                echo '<body>';
                    echo '<div class="container">';
                        echo '<div class="error">';
                            echo '<h2>';

                                if ($object->class != null)
                                    echo $object->class . ': ';

                                if ($object->isErrors)
                                    echo $object->label . ': ';

                                echo $object->message;
                            echo '</h2>';
                        echo '</div>';

                        echo '<ul class="list">';
                            echo '<li>in <span class="file">' . $object->file . ' line <strong class="line">' . $object->line . '</strong></span></li>';

                            $entryClass    = null;
                            $entryType     = null;
                            $entryFunction = null;
                            $entryFile     = null;
                            $entryLine     = null;

                            if (is_array($object->trace) && count($object->trace) > 0) {
                                foreach ($object->trace AS $i => $entry) {
                                    if (isset($entry['class']))    $entryClass    = $entry['class'];
                                    if (isset($entry['type']))     $entryType     = $entry['type'];
                                    if (isset($entry['function'])) $entryFunction = $entry['function'] . '(' . Librarys\Http\Error\Display::argsExport($entry['args']) . ')';
                                    if (isset($entry['file']))     $entryFile     = ' in <span class="file">' . $entry['file'];
                                    if (isset($entry['line']))     $entryLine     = ' line <strong class="line">' . $entry['line'] . '</strong></span>';

                                    echo '<li>at ' . $entryClass . $entryType . $entryFunction . $entryFile . $entryLine . '</li>';

                                    $entryClass    = null;
                                    $entryType     = null;
                                    $entryFunction = null;
                                    $entryFile     = null;
                                    $entryLine     = null;
                                }
                            }
                        echo '</ul>';
                    echo '</div>';
                echo '</body>';
            echo '</html>';
        }

        public static function argsExport($args)
        {
            if (is_array($args) == false || count($args) <= 0)
                return null;

            $buffer = null;
            $count  = count($args);

            foreach ($args AS $index => $arg) {
                if (is_array($arg))
                    $buffer .= 'array(' . self::argsExport($arg) . ')';
                else if (is_object($arg))
                    $buffer .= 'object(<span class="class">' . get_class($arg) . '</span>)';
                else if (is_resource($arg))
                    $buffer .= 'resource(<span class="resource">' . get_resource_type($arg) . '</span>)';
                else if (is_string($arg))
                    $buffer .= '\'' . $arg . '\'';
                else if (is_null($arg))
                    $buffer .= 'null';
                else
                    $buffer .= $arg;

                if ($index + 1 < $count)
                    $buffer .= ', ';
            }

            return $buffer;
        }
    }
