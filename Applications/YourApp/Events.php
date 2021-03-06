<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Protocols\GatewayProtocol;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * Notes: websocket握手连接
     *
     * Author: chentulin
     * DateTime: 2021/3/5 22:55
     * E-MAIL: <chentulinys@163.com>
     * @param $client_id
     * @param $data
     */
    public static function onWebSocketConnect($client_id, $data)
    {
        $message = '{"type":"onConnect","to_client_id":"'. $client_id .'","content":"连接成功"}';
        Gateway::sendToClient($client_id, $message);
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
//    public static function onConnect($client_id)
//    {
//        // 向当前client_id发送数据
//        Gateway::sendToClient($client_id, "Hello $client_id\r\n");
//        // 向所有人发送
//        Gateway::sendToAll("$client_id login\r\n");
//    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       // CI过来进行解码
       $arr = GatewayProtocol::decode($message);
       // 转化
       $data = json_decode($arr['body']);
       if (!$data){
           return;
       }
       $dataArr = GatewayProtocol::object_array($data);
       // 如果是向某个客户端发送消息
       if(isset($dataArr['type']) && $dataArr['type'] === 'onClose')
       {
           var_export($dataArr);
           var_export($dataArr['to_client_id']);
           var_export($dataArr['content']);
           Gateway::sendToClient($dataArr['to_client_id'],$dataArr['content']);
       }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       $message = '{"type":"onClose","to_client_id":"'. $client_id .'","content":"退出登录"}';
       Gateway::sendToClient($client_id, $message);
   }

}
