<?php
namespace beckeross\jsonstyler;

use PHPUnit\Framework\TestCase;
use beckeross\jsonstyler\Repositories\JsonStylerRepository;
use ReflectionClass;

class JsonStylerRepositoryTest extends TestCase
{
    protected JsonStylerRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new JsonStylerRepository();
    }

    public function testLoadThemesOverridesDefault()
    {
        $themes = [
            'default' => ['braces' => 'blue'],
            'dark' => ['braces' => 'black'],
        ];
        $this->repo->loadThemes($themes);

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('themes');
        $property->setAccessible(true);

        $this->assertSame($themes, $property->getValue($this->repo));
    }

    public function testSetThemeChangesCurrentTheme()
    {
        $themes = [
            'default' => ['braces' => 'blue'],
            'dark' => ['braces' => 'black'],
        ];
        $this->repo->loadThemes($themes);
        $this->repo->setTheme('dark');

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('currentTheme');
        $property->setAccessible(true);

        $this->assertSame('dark', $property->getValue($this->repo));
    }

    public function testSetThemeIgnoresInvalidTheme()
    {
        $themes = ['default' => ['braces' => 'blue']];
        $this->repo->loadThemes($themes);
        $this->repo->setTheme('nonexistent');

        $reflection = new ReflectionClass($this->repo);
        $property = $reflection->getProperty('currentTheme');
        $property->setAccessible(true);

        $this->assertSame('default', $property->getValue($this->repo));
    }

    public function testGetThemeReturnsMergedAndSanitizedTheme()
    {
        $themes = [
            'default' => [
                'braces' => '<script>alert("xss")</script>',
                'custom_keywords' => [
                    'ERROR' => '<b>red</b>',
                ],
            ],
        ];
        $this->repo->loadThemes($themes);
        $theme = $this->repo->getTheme();

        $this->assertArrayHasKey('braces', $theme);
        $this->assertSame(
            htmlspecialchars('<script>alert("xss")</script>', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $theme['braces']
        );
        $this->assertSame(
            htmlspecialchars('<b>red</b>', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $theme['custom_keywords']['ERROR']
        );
    }

    public function testFormatJsonWithValidArray()
    {
        $data = ['name' => 'John', 'age' => 30];
        $output = $this->repo->formatJson($data);

        $this->assertStringContainsString('<pre><code class=\'json\'>', $output);
        $this->assertStringContainsString('"name"', $output);
        $this->assertStringContainsString('John', $output);
        $this->assertStringContainsString('age', $output);
        $this->assertStringContainsString('30', $output);
    }

    public function testFormatJsonWithValidJsonString()
    {
        $data = '{"name":"John","age":30}';
        $output = $this->repo->formatJson($data);

        $this->assertStringContainsString('<pre><code class=\'json\'>', $output);
        $this->assertStringContainsString('"name"', $output);
        $this->assertStringContainsString('John', $output);
        $this->assertStringContainsString('age', $output);
        $this->assertStringContainsString('30', $output);
    }

    public function testFormatJsonWithInvalidJsonString()
    {
        $data = '{"name":John}';
        $output = $this->repo->formatJson($data);

        $this->assertStringContainsString('Invalid JSON:', $output);
    }

    public function testFormatJsonWithNullReturnsNotice()
    {
        $output = $this->repo->formatJson(null);
        $this->assertStringContainsString('No data available.', $output);
    }

    public function testFormatJsonWithCustomKeywords()
    {
        $themes = [
            'default' => [
                'custom_keywords' => [
                    'ERROR' => 'red'
                ]
            ]
        ];
        $this->repo->loadThemes($themes);

        $data = ['status' => 'ERROR'];
        $output = $this->repo->formatJson($data);

        $this->assertStringContainsString("<span style='color: red;'>ERROR</span>", $output);
    }

    public function testFormatJsonWithIncompleteTheme()
    {
        $theme = [
            'brackets' => 'darkgreen',
            'keys' => 'purple',
            'values' => 'darkgreen',
            'background'=> 'bg-white',
            'custom_keywords' => [
                'ERROR' => 'red',
                'issues' => 'red',
            ],
        ];

        $this->repo->loadThemes(['default' => $theme]);
        $output = $this->repo->formatJson('{"a":"hello", "b":"world"}');

        // Fallback-Color for 'braces' (not in given theme included)
        $this->assertStringContainsString("color: darkorange", $output);
    }

    public function testFormatJsonWithHtmlContent()
    {
        $data = [
            'a' => 'hello',
            'b' => 'world',
            'description' => '<h1>Hello world</h1><div>Lorem ipsum tralala</div>',
        ];
        $output = $this->repo->formatJson($data);

        // HTML is escaped
        $this->assertStringNotContainsString('<h1>', $output);
        $this->assertStringContainsString(htmlspecialchars('<h1>Hello world</h1>', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $output);
    }

    public function testFormatJsonWithJavaScriptInJson()
    {
        $data = [
            'a' => 'hello',
            'b' => "world<script>document.body.append(document.createElement('div').textContent='🔥This is fine...🔥')</script>",
        ];
        $output = $this->repo->formatJson($data);
        // JavaScript is escaped
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString(htmlspecialchars($data['b'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $output);
    }

    public function testFormatJsonWithJavaScriptInTheme()
    {
        $themes = [
            'default' => [
                'braces' => 'darkorange',
                'brackets' => 'darkgreen',
                'keys' => 'purple',
                'values' => 'darkgreen',
                'background'=> 'bg-white',
                'custom_keywords' => [
                    'issues' => "red;'><script>document.body.append(document.createElement('div').textContent='🔥')</script><style='",
                ],
            ]
        ];

        $this->repo->loadThemes($themes);
        $output = $this->repo->formatJson(['a' => 'hello', 'issues' => 'none']);

        // JavaScript in themes are escaped
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString(htmlspecialchars($themes['default']['custom_keywords']['issues'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $output);
    }

}
