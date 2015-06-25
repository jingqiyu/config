<?php 
class LruCache
{
    private $_arrData;
    private $_size;
    private $_count;
    private $_hit;
    private $_request;

    public function __construct(){
        $this->_size = 300;
        $this->_count = 0;
        $this->_arrData = array();
        $this->_hit = 0;
        $this->_request=0;
    }

    public function getItem( $hashKey ) {
        $this->_request++;
        if( $this->_count == $this->_size ){
            
            if ( in_array($hashKey, $this->_arrData) ) {

                $index = array_search( $hashKey, $this->_arrData );
                array_splice( $this->_arrData, $index , 1);
                array_unshift( $this->_arrData, $hashKey );
                $this->_hit++;

            } else {

                array_splice( $this->_arrData, $this->_size-1, 1);
                array_unshift( $this->_arrData, $hashKey );
            }

        } else {

            if ( in_array($hashKey, $this->_arrData) ) {

                $index = array_search( $hashKey, $this->_arrData );
                array_splice( $this->_arrData, $index , 1);
                $this->_hit++;

            } else {
                
                array_unshift( $this->_arrData, $hashKey );
                $this->_count++;

            }
        }

    }

    public function showCachePool(){
        print_r( $this->_arrData );
    }

    public function getHitPercent(){
        $percent = floatval($this->_hit)/floatval($this->_request)*100;
        return $percent."%";

    }

    public function cleanCachePoll(){
        $this->_count = 0;
        $this->_arrData = array();
        $this->_hit = 0;
        $this->_request=0;
    }

    public function getRequest() {
        return $this->_request;
    }

    public function getHit() {
        return $this->_hit;
    }
}

    $objLru = new LruCache();
    $requestTotal = 10000;
    $arrReq = array();
    for ( $i=0;$i<(int)$requestTotal*0.8;$i++ ) {
        $arrReq[] =  rand( 1, 200 );
    }
    for ( $i=0; $i<(int)$requestTotal*0.2; $i++ ) {
        $arrReq[] = rand( 200, 1000 );
    }
    shuffle( $arrReq );
    foreach ($arrReq as $key => $value) {
        $objLru->getItem( $value );
    }
    $floatPercent = $objLru->getHitPercent();
    echo $floatPercent.PHP_EOL;
?>
