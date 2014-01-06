<?php

namespace Drupal\at_base\Tests;

/**
 * cache_get()/cache_set() does not work on unit test cases.
 */
class TwigTest extends \DrupalWebTestCase {
  public function getInfo() {
    return array(
      'name' => 'AT Base: Twig Service',
      'description' => 'Test Twig service',
      'group' => 'AT Base'
    );
  }

  public function setUp() {
    $this->profile = 'testing';
    parent::setUp('atest_base', 'atest2_base');
  }

  public function testServiceContainer() {
    $twig = at_container('twig');
    $this->assertEqual('Twig_Environment', get_class($twig));
  }

  public function testDefaultFilters() {
    $twig = at_container('twig');
    $filters = $twig->getFilters();

    foreach (array('render', 't', 'url', '_filter_autop', 'drupalBlock', 'drupalEntity', 'drupalView', 'at_config') as $filter) {
      $this->assertTrue(isset($filters[$filter]), "Found filter {$filter}");
    }
  }

  public function testContentRender() {
    $render = at_container('helper.content_render');

    // Simple string
    $expected = 'Hello Andy Truong';
    $actual = $render->setData($expected)->render();
    $this->assertEqual($expected, $actual);

    // Template string
    $data['template_string'] = 'Hello {{ name }}';
    $data['variables']['name'] = 'Andy Truong';
    $output = $render->setData($data)->render();
    $this->assertEqual($expected, $actual);

    // Template
    $data['template'] = '@atest_theming/templates/hello.twig';
    $data['variables']['name'] = 'Andy Truong';
    $output = $render->setData($data)->render();
    $assert = strpos($output, $actual) !== FALSE;
    $this->assertTrue($assert, "Found <strong>{$expected}</strong> in result.");
  }

  public function testTwigFilters() {
    $output = \AT::twig_string()->render("{{ 'user:1' | drupalEntity }}");
    $this->assertTrue(strpos($output, 'History'), 'Found text "History"');
    $this->assertTrue(strpos($output, 'Member for'), 'Found text: "Member for"');
  }

  public function testTwigStringLoader() {
    $output = \AT::twig_string()->render('Hello {{ name }}', array('name' => 'Andy Truong'));
    $this->assertEqual('Hello Andy Truong', $output, 'Template string is rendered correctly.');
  }

  public function testCacheFilter() {
    $string_1  = "{% set options = { cache_id: 'atestTwigCache:1' } %}";
    $string_1 .= "\n {{ 'atest_base.service_1:hello' | cache(options) }}";
    $string_2  = "{% set options = { cache_id: 'atestTwigCache:2' } %}";
    $string_2 .= "\n {{ 'At_Base_Test_Class::helloStatic' | cache(options) }}";
    $string_3  = "{% set options = { cache_id: 'atestTwigCache:3' } %}";
    $string_3 .= "\n {{ 'atest_base_hello' | cache(options) }}";
    $string_4  = "{% set options  = { cache_id: 'atestTwigCache:4' } %}";
    $string_4 .= "\n {% set callback = { callback: 'atest_base_hello', arguments: ['Andy Truong'] } %}";
    $string_4 .= "\n {{ callback | cache(options) }}";
    for ($i = 1; $i <= 4; $i++) {
      $expected = 'Hello Andy Truong';
      $actual = "string_{$i}";
      $actual = at_container('twig_string')->render($$actual);
      $actual = trim($actual);
      $this->assertEqual($expected, $actual);
    }
  }
}

// class At_Base_Cache_Views_Warmer extends DrupalWebTestCase {
//   public function getInfo() {
//     return array(
//       'name' => 'AT Theming: AT Cache > Views-Cache warmer',
//       'description' => 'Try views-cache warmer of at_base.',
//       'group' => 'AT Theming',
//     );
//   }

//   protected function setUp() {
//     parent::setUp('atest_theming');
//   }

//   /**
//    * @todo test me.
//    */
//   public function testViewsCacheWarming() {
//     // Build the first time
//     // $output = at_id(new Drupal\at_base\Helper\SubRequest('atest_theming/users'))->request();
//     $output = views_embed_view('atest_theming_user', 'page_1');

//     // Invoke entity save event
//     $u = $this->drupalCreateUser();

//     // Build the second time
//     // $output = at_id(new Drupal\at_base\Helper\SubRequest('atest_theming/users'))->request();
//     $output = views_embed_view('atest_theming_user', 'page_1');
//     $this->assertTrue(FALSE !== strpos($output, $u->name), "Found {$u->name}");

//     $this->verbose(print_r(_cache_get_object('cache_views_data'), TRUE));
//   }
// }
