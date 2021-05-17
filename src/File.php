<?php

namespace W7\Validate\Ide\Helper;

/**
 * 文件读取类
 * @package W7\Engine\Core\Exceptions\Trace
 */
class File
{
    /**
     * 当前读取的文件
     * @var array
     */
    private $file;

    public function __construct(string $path)
    {
        $this->file = explode("\n", file_get_contents($path));
    }
    
    /**
     * 获取指定行的内容
     * @param int $line
     * @return string
     */
    public function readLine(int $line): string
    {
        return $this->file[$line - 1] ?? '';
    }
}
