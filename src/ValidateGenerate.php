<?php

namespace Itwmw\Validate\Ide\Helper;

use Itwmw\Validate\Middleware\ValidateMiddlewareConfig;
use ReflectionClass;
use ReflectionMethod;
use W7\Validate\Validate;

class ValidateGenerate
{
    /** @var string  */
    protected $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function generateValidatorCommon()
    {
        $reflection = new ReflectionClass($this->class);
        if (!$reflection->isInstantiable()) {
            return;
        }

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        $ignore = [
            '__get', '__set', '__isset', '__unset', '__call', '__autoload', '__construct', '__destruct',
            '__clone', '__toString ', '__sleep', '__wakeup', '__set_state', '__invoke', '__callStatic'
        ];

        $filename = $reflection->getFileName();
        $file     = new File($filename);
        $php      = file_get_contents($filename);

        foreach ($methods as $method) {
            if (in_array($method->getName(), $ignore)) {
                continue;
            }
            if ($method->getDeclaringClass()->getName() != $reflection->getName()) {
                continue;
            }
            if ($method->getFileName() !== $filename) {
                continue;
            }

            try {
                $validate = ValidateMiddlewareConfig::instance()->getValidateFactory()->getValidate($reflection->getName(), $method->getName());
                if ($validate instanceof Validate) {
                    $validateClass = get_class($validate);
                    $scene         = $validate->getCurrentSceneName();
                    if (method_exists($validate, 'scene' . ucfirst($scene))) {
                        $validateClass .= '::scene' . ucfirst($scene) . '()';
                    } else {
                        $validateClass .= '::$scene';
                    }
                    $validateClass = '\\' . $validateClass;
                } else {
                    $validateClass = null;
                }

                $methodDefine = $file->readLine($method->getStartLine());
                $publicPrefix = $this->getPublicPrefix($methodDefine);
                $newCommon    = $this->makeDocComment($method, $validateClass, $publicPrefix);

                if (false !== $newCommon) {
                    $oldCommon = $method->getDocComment();
                    if ($oldCommon) {
                        $oldCommonLine = count(explode("\n", $oldCommon));
                        $seq           = $this->getBetweenTexts($oldCommon, $methodDefine, $file->readContent($method->getStartLine() - $oldCommonLine - 1, $method->getStartLine()));
                        $php           = str_replace($oldCommon . $seq . $methodDefine, $newCommon . $seq . $methodDefine, $php);
                    } else {
                        $php = str_replace($methodDefine, $publicPrefix . $newCommon . PHP_EOL . $methodDefine, $php);
                    }
                }
            } catch (\Exception $e) {
                echo $e;
            }
        }

        file_put_contents($filename, $php);
    }

    private function getBetweenTexts($start, $end, $str): string
    {
        $startPos = mb_strpos($str, $start) + mb_strlen($start);
        $endPos   = mb_strpos($str, $end) - $startPos;
        return mb_substr($str, $startPos, $endPos);
    }

    private function makeDocComment(ReflectionMethod $reflection, ?string $newComment, string $publicPrefix)
    {
        $doc  = $reflection->getDocComment();
        $docs = explode("\n", $doc);

        $validatePos = -1;
        for ($i = 0; $i < count($docs); $i++) {
            if (str_contains($docs[$i], '@validate')) {
                $validatePos = $i;
                break;
            }
        }

        if (null === $newComment) {
            if (-1 === $validatePos) {
                return false;
            }
            unset($docs[$validatePos]);
            return implode("\n", $docs);
        }
        $newValidateDocComment = "@validate {@see $newComment}";

        if (-1 !== $validatePos) {
            $commonPrefixPos    = strrpos($docs[$validatePos], '@validate');
            $commonPrefix       = substr($docs[$validatePos], 0, $commonPrefixPos);
            $docs[$validatePos] = $commonPrefix . $newValidateDocComment;
        } else {
            if (count($docs) > 2) {
                $commonPrefixPos = strrpos($docs[1], '*');
                $commonPrefix    = substr($docs[1], 0, $commonPrefixPos);
                array_splice($docs, count($docs) - 1, 0, [$commonPrefix . '* ' . $newValidateDocComment]);
            } else {
                $commonName = '';
                echo $doc;
                if (preg_match('/\/\*\*([\s\S]*?)\*/', $doc, $match) > 0) {
                    var_dump($match);
                    $commonName = $match[1];
                    $commonName = trim(str_replace("\n", '', $commonName));
                }
                $docs   = [];
                $docs[] = '/**';
                if (!empty($commonName)) {
                    $docs[] = $publicPrefix . '* ' . $commonName;
                }
                $docs[] = $publicPrefix . '* ' . $newValidateDocComment;
                $docs[] = $publicPrefix . '*/';
            }
        }

        return implode("\n", $docs);
    }

    private function getPublicPrefix(string $methodDefine)
    {
        $publicPos = strrpos($methodDefine, 'public');
        return substr($methodDefine, 0, $publicPos);
    }
}
