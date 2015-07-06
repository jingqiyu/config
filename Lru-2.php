<?php
error_reporting(0);
class LruCache
{
    private $_cachePool;
    private $_sizeCachePool;
    private $_countCachePool;
    private $_tmpCachePool;
    private $_sizeTmpCachePool;     // 第一队列空间大小
    private $_countTmpCachePool;    // 第一队存储数量
    private $_hit;
    private $_request;
    private $_k;

    public function __construct(){
        $this->_sizeCachePool = 300;
        $this->_countCachePool = 0;
        $this->_cachePool = array();
        $this->_sizeTmpCachePool = 300;
        $this->_countTmpCachePool = 0;
        $this->_tmpCachePool = array();
        $this->_hit = 0;
        $this->_request=0;
        $this->_k = 2;
    }

    public function getItem( $hashKey ) {
        $this->_request++;
        
        // 命中核心缓冲池
        if ( in_array($hashKey, $this->_cachePool) ) {

            $index = array_search( $hashKey, $this->_cachePool );
            array_splice( $this->_cachePool, $index , 1);
            array_unshift( $this->_cachePool, $hashKey );
            $this->_hit++;
        // 未命中核心缓冲，查看临时缓冲区中是否存在
        } else {
                // 命中临时缓冲
            if( array_key_exists($hashKey, $this->_tmpCachePool) ) {
                if( ++$this->_tmpCachePool[$hashKey] == $this->_k ){
                    //删除临时缓冲区里的键值，并移动到缓冲区中去，如果满则淘汰，不满则直接插入
                    $this->insert2CachePool($hashKey);
                    unset($this->_tmpCachePool[$hashKey]);
                }
            } else {
                // 需要淘汰掉次数最少的
                if( $this->_countTmpCachePool == $this->_sizeTmpCachePool ) {
                    asort($this->_tmpCachePool); 
                    $this->_tmpCachePool = array_reverse($this->_tmpCachePool,true);
                    array_pop($this->_tmpCachePool);
                } else{
                    $this->_countTmpCachePool++;
                }
                // 把新的元素加到缓冲区里来
                $this->_tmpCachePool[$hashKey]= 1;
            }

        } 

    }

    public function insert2CachePool($hashKey) {

        if( $this->_countCachePool == $this->_sizeCachePool ){

            array_splice( $this->_cachePool, $this->_size-1, 1);
            array_unshift( $this->_cachePool, $hashKey );

        } else {
            
            array_unshift( $this->_cachePool, $hashKey );
            $this->_countCachePool++;
        }
    }
   

    public function showCachePool(){
        print_r( $this->_tmpCachePool );
        echo "-------------------------------------------------\n";
        print_r( $this->_cachePool );
        echo "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n";
    }

    public function getHitPercent(){
        echo "Hit    : {$this->_hit}\n";
        echo "Request: {$this->_request}\n";
        $percent = floatval($this->_hit)/floatval($this->_request)*100;
        return $percent."%";

    }

    public function getRequest() {
        return $this->_request;
    }

    public function getHit(){
        return $this->_hit;
    }
}

$objLru = new LruCache();
$requestTotal = 50000;
$arrReq = array();
for($i=0;$i<(int)$requestTotal*0.8;$i++){
    $arrReq[] =  rand(1,200);
}
for($i=0;$i<(int)$requestTotal*0.2;$i++){
    $arrReq[] = rand(200,1000);
}
shuffle($arrReq);
$j=0;
foreach ($arrReq as $key => $value) {
    $objLru->getItem($value);
    if(++$j%10000==0){
        sleep(0);
    }
}

$floatPercent = $objLru->getHitPercent();
echo $floatPercent.PHP_EOL;




