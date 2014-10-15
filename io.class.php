<?php
/**
 * IO 处理
 *
 * @package    classes
 * @author     zhongyy <regulusyun@gmail.com>
 * @copyright  Copyright (c) 2010-10-5
 * @license    http://www.gnu.org/licenses/gpl.html     GPL 3
 */
abstract class Io
{

    /**
     * 修正路径分隔符为操作系统的正确形式
     *
     * @param  string  $path
     * @return string
     */
    public static function strip($path)
    {
        return preg_replace('/[\\\\\/]+/', DIRECTORY_SEPARATOR, (string) $path);
    }

    /**
     * 创建目录(递归创建)
     *
     * @param  string  $dir   目录路径
     * @param  int     $mode  访问权限
     * @return string|FALSE
     */
    public static function mkdir($dir, $mode = 0777)
    {
        $dir = Io::strip($dir);

        if ( ! is_dir($dir))
        {
            $mk = @mkdir($dir, 0777, TRUE);
            if ($mk === FALSE)
            {
                return FALSE;
            }
        }

        return $dir;
    }

    /**
     * 删除目录(递归删除)
     *
     * @param  string  $dir
     * @return bool
     */
    public static function rmdir($dir)
    {
        $dir = Io::strip($dir);

        if (is_dir($dir))
        {
            $dirs = Io::scan($dir);
            if (is_array($dirs))
            {
                $flag = TRUE;
                foreach ($dirs as $file)
                {
                    $file = "$dir/$file";
                    if (is_dir($file))
                    {
                        $flag = Io::rmdir($file);
                    }
                    else
                    {
                        $flag = @unlink($file);
                    }
                    if ($flag == FALSE)
                    {
                        break;
                    }
                }
            }
            return @rmdir($dir);
        }

        return FALSE;
    }

    /**
     * 扫描目录下所有的文件/目录
     *
     * @param  string  $dir     指定的目录
     * @param  array   $ignore  需要跳过的文件/目录
     * @return array|FALSE
     */
    public static function scan($dir, array $ignore = array('.svn'))
    {
        $dir = Io::strip($dir);

        if (is_dir($dir))
        {
            $dirs = scandir($dir);
            foreach ($dirs as $k => $v)
            {
                if ($v == '.' OR $v == '..' OR in_array($v, $ignore))
                {
                    unset($dirs[$k]);
                }
            }
            return $dirs;
        }

        return FALSE;
    }

    /**
     * 复制文件/目录
     *
     * @param  string   $from      源文件/目录
     * @param  string   $to        目标文件/目录
     * @param  boolean  $override  是否覆盖
     * @param  array    $ignore    需要跳过的文件/目录
     * @return boolean
     */
    public static function copy($from, $to, $override = TRUE, array $ignore = array('.svn'))
    {
        $from = Io::strip($from);
        $to   = Io::strip($to);

        if (is_file($from))
        {
            Io::mkdir(dirname($to));
            if (is_file($to) AND ! $override) // 已经存在且不允许覆盖
            {
                return TRUE;
            }
            else
            {
                return @copy($from, $to);
            }

        }
        elseif (is_dir($from))
        {
            $dirs = Io::scan($from, $ignore);
            if (is_array($dirs))
            {
                foreach ($dirs as $file)
                {
                    Io::copy("$from/$file", "$to/$file", $override, $ignore);
                }
            }

            return TRUE;
        }

        return FALSE;
    }

    /**
     * 递归扫描目录
     */
    public static function get_file($path){
        $tree = array();
        foreach(glob($path.'/*') as $single){
            if(is_dir($single)){
                $tree = array_merge($tree, self::get_file($single));
            }else{
                $tree[] = self::strip($single);
            }
        }
        return $tree;
    }

}