<?php

include_once 'vendor/autoload.php';

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use W7\Validate\Ide\Helper\Validate;

class UserValidate
{
    protected $scene = [];
    protected function sceneA()
    {
    }
    public function sceneTest()
    {
    }
}
class Test
{

	/**
	 * @param int $a
	 * @param $b
	 * @return mixed
	 * @throws Exception
	 */
    public function hello(int $a, $b)
    {
        if (100 == $a) {
            throw new Exception('asd');
        }

        return $b;
    }
}


$ref = new ReflectionClass(Test::class);
$method = $ref->getMethod('hello');
$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($method->getDocComment());
$tags = $docblock->getTags();

for ($i = 0 ;$i < 5;$i++){
	echo $i;
}