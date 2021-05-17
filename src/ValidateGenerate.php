<?php

namespace W7\Validate\Ide\Helper;

use ReflectionClass;
use ReflectionMethod;
use W7\Validate\Support\Storage\ValidateFactory;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;

class ValidateGenerate
{
    /** @var ReflectionClass  */
    protected $reflection;
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;
    /**
     * @var DescriptionFactory
     */
    private $descriptionFactory;

    public function __construct(string $class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('class：' . $class . ' does not exist');
        }
        $this->reflection = new ReflectionClass($class);
        $this->createDocBlockFactory();
    }

    protected function getAllMethod(): array
    {
        return $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    }

    public function getValidator()
    {
        if (!$this->reflection->isInstantiable()) {
            // 忽略接口和抽象类
            return;
        }

        $ignore = [
            '__get', '__set', '__isset', '__unset', '__call', '__autoload', '__construct', '__destruct',
            '__clone', '__toString ', '__sleep', '__wakeup', '__set_state', '__invoke', '__callStatic'
        ];

        $filename = $this->reflection->getFileName();
        $file     = new File($filename);
        /** @var ReflectionMethod $method */
        foreach ($this->getAllMethod() as $method) {
            if (in_array($method->getName(), $ignore)) {
                break;
            }
            try {
                $validate = ValidateFactory::getValidate($this->reflection->getName(), $method->getName());
                if ($validate instanceof \W7\Validate\Validate) {
                    $validateClass = get_class($validate);
                    $scene         = $validate->getCurrentSceneName();
                    $php           = file_get_contents($filename);
                    $oldCommon     = $method->getDocComment();

                    if (method_exists($validate, 'scene' . ucfirst($scene))) {
                        $validateClass .= '::scene' . ucfirst($scene);
                    } else {
                        $validateClass .= '::$scene';
                    }

                    $newCommon = $this->makeDocComment($method, $validateClass);

                    $methodDefine = $file->readLine($method->getStartLine());
                    $newCommon    = $this->alignedCommon($methodDefine, $newCommon);

                    if ($oldCommon) {
                        $php = str_replace($oldCommon, $newCommon, $php);
                    } else {
                        $pos = strpos($php, $methodDefine);
                        if (false !== $pos) {
                            $php = substr_replace($php, $newCommon . PHP_EOL . $methodDefine, $pos, strlen($methodDefine));
                        }
                    }

                    file_put_contents($filename, $php);
                }
            } catch (\Exception $e) {
                var_dump($e);
            }
        }

        $this->reflection = new ReflectionClass($this->reflection->getName());
    }

    private function alignedCommon(string $methodDefine, string $common)
    {
        $publicPos = strrpos($methodDefine, 'public');
        $prefix    = substr($methodDefine, 0, $publicPos);
        $commons   = explode("\n", $common);
        for ($i = 0; $i < count($commons); $i++) {
            if (0 === $i) {
                continue;
            }
            $commons[$i] = $prefix . $commons[$i];
        }
        return implode("\n", $commons);
    }

    private function createDocBlockFactory()
    {
        $fqsenResolver      = new FqsenResolver();
        $tagFactory         = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);

        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService(new TypeResolver($fqsenResolver));

        $docBlockFactory = new DocBlockFactory($descriptionFactory, $tagFactory);
        $docBlockFactory->registerTagHandler('validate', Validate::class);

        $this->docBlockFactory    = $docBlockFactory;
        $this->descriptionFactory = $descriptionFactory;
    }

    private function makeDocComment(ReflectionMethod $reflection, string $newComment): string
    {
        $context     = (new ContextFactory())->createFromReflector($reflection);
        $phpdoc      = $this->docBlockFactory->create($reflection, $context);
        $docTags     = $phpdoc->getTags();
        $validatePos = $returnPos = $throwsPos = -1;
        for ($i = 0; $i < count($docTags); $i++) {
            $tag = $docTags[$i];
            if ($tag instanceof Validate) {
                $validatePos = $i;
                $phpdoc->removeTag($tag);
                break;
            } elseif ($tag instanceof Return_) {
                $returnPos = $i;
            } elseif ($tag instanceof Throws) {
                $throwsPos = $i;
            }
        }

        $newValidateTag = Validate::create("{@see $newComment}", $this->descriptionFactory);
        if (-1 !== $validatePos) {
            $docTags[$validatePos] = $newValidateTag;
        } elseif (-1 !== $returnPos) {
            array_splice($docTags, $returnPos, 0, [$newValidateTag]);
        } elseif (-1 !== $throwsPos) {
            array_splice($docTags, $throwsPos, 0, [$newValidateTag]);
        } else {
            $docTags[] = $newValidateTag;
        }

        $phpDocs = new DocBlock($phpdoc->getSummary(), $phpdoc->getDescription(), $docTags, $context);

        $serializer = new Serializer();
        return $serializer->getDocComment($phpDocs);
    }
}
