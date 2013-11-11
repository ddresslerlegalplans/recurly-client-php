<?php

require_once(__DIR__ . '/../test_helpers.php');

class Recurly_CouponTest extends Recurly_TestCase
{
  public function testGetCoupon() {
    $this->client->addResponse('GET', '/coupons/special', 'coupons/show-200.xml');

    $coupon = Recurly_Coupon::get('special', $this->client);

    $this->assertInstanceOf('Recurly_Coupon', $coupon);
    $this->assertEquals('special', $coupon->coupon_code);
    $this->assertEquals(1304150400, $coupon->created_at->getTimestamp());
    $this->assertEquals('https://api.recurly.com/v2/coupons/special', $coupon->getHref());
    $this->assertInstanceOf('Recurly_CurrencyList', $coupon->discount_in_cents);
    $this->assertEquals(1000, $coupon->discount_in_cents['USD']->amount_in_cents);
  }

  public function testDeleteCoupon() {
    $this->client->addResponse('DELETE', '/coupons/special', 'coupons/destroy-204.xml');

    Recurly_Coupon::deleteCoupon('special', $this->client);
  }

  // Parse plan_codes array in response
  public function testPlanCodesXml() {
    $this->client->addResponse('GET', '/coupons/special', 'coupons/show-200-2.xml');

    $coupon = Recurly_Coupon::get('special', $this->client);

    $this->assertInstanceOf('Recurly_Coupon', $coupon);
    $this->assertCount(2, $coupon->plan_codes);
    $this->assertEquals($coupon->plan_codes[0], 'plan_one');
    $this->assertEquals($coupon->plan_codes[1], 'plan_two');
  }

  public function testXml() {
    $coupon = new Recurly_Coupon();
    $coupon->coupon_code = 'fifteen-off';
    $coupon->name = '$15 Off';
    $coupon->discount_type = 'dollar';
    $coupon->discount_in_cents->addCurrency('USD', 1500);

    $this->assertEquals(
      "<?xml version=\"1.0\"?>\n<coupon><coupon_code>fifteen-off</coupon_code><name>$15 Off</name><discount_type>dollar</discount_type><discount_in_cents><USD>1500</USD></discount_in_cents></coupon>\n",
      $coupon->xml()
    );
  }

  public function testXmlWithPlans() {
    $coupon = new Recurly_Coupon();
    $coupon->coupon_code = 'fifteen-off';
    $coupon->name = '$15 Off';
    $coupon->discount_type = 'dollar';
    $coupon->discount_in_cents->addCurrency('USD', 1500);
    $coupon->plan_codes = array('gold', 'monthly');

    $this->assertEquals(
      "<?xml version=\"1.0\"?>\n<coupon><coupon_code>fifteen-off</coupon_code><name>$15 Off</name><discount_type>dollar</discount_type><discount_in_cents><USD>1500</USD></discount_in_cents><plan_codes><plan_code>gold</plan_code><plan_code>monthly</plan_code></plan_codes></coupon>\n",
      $coupon->xml()
    );
  }
}