<?php
defined('UNIT_TESTS_ROOT') || require __DIR__ . '/bootstrap.php';

class CounterTest extends TestCase
{
    protected $_di;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->_di = new ManaPHP\Di\FactoryDefault();
        $this->_di->setShared('redis', function () {
            $redis = new \Redis();
            $redis->connect('localhost');
            return $redis;
        });
    }

    public function test_get()
    {
        $counter = new ManaPHP\Counter\Adapter\Redis();

        $counter->delete('c', '1');

        $this->assertEquals(0, $counter->get('c', '1'));
        $counter->increment('c', '1');
        $this->assertEquals(1, $counter->get('c', '1'));
    }

    public function test_increment()
    {
        $counter = new ManaPHP\Counter\Adapter\Redis();
        $counter->delete('c', '1');
        $this->assertEquals(1, $counter->increment('c', '1'));
        $this->assertEquals(2, $counter->increment('c', '1', 1));
        $this->assertEquals(22, $counter->increment('c', '1', 20));
        $this->assertEquals(2, $counter->increment('c', '1', -20));

        $counter->delete('c', 1);
        $this->assertEquals(0, $counter->get('c', 1));
    }

    public function test_decrement()
    {
        $counter = new ManaPHP\Counter\Adapter\Redis();
        $counter->delete('c', '1');
        $this->assertEquals(-1, $counter->decrement('c', '1'));
        $this->assertEquals(-2, $counter->decrement('c', '1', 1));
        $this->assertEquals(-22, $counter->decrement('c', '1', 20));
        $this->assertEquals(-2, $counter->decrement('c', '1', -20));
    }

    public function test_delete()
    {
        $counter = new ManaPHP\Counter\Adapter\Redis();
        $counter->delete('c', '1');

        $counter->increment('c', '1');
        $counter->delete('c', '1');
    }

}