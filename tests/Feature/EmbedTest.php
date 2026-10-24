<?php

use App\Models\Embed;
use App\Models\User;
use App\Services\EmbedService;
use App\Services\MarkdownService;
use Livewire\Livewire;

function makeEmbed(User $user, string $title = 'Simulador', string $html = '<body><h1>Hola</h1><script>let a = 1;</script></body>'): Embed
{
    return app(EmbedService::class)->createEmbed($user, compact('title', 'html'));
}

test('the raw page is served sandboxed, with its scripts intact', function () {
    $embed = makeEmbed(User::factory()->create());

    $response = $this->get(route('embeds.raw', $embed));

    $response->assertOk();
    $response->assertHeader('Content-Security-Policy', 'sandbox '.EmbedService::SANDBOX."; frame-ancestors 'self'");
    $response->assertSee('<script>let a = 1;</script>', false);
    $response->assertSee('rednotesEmbedHeight', false);
    expect(EmbedService::SANDBOX)->not->toContain('allow-same-origin');
});

test('guests can view an embed inside the app layout but not its source editor', function () {
    $embed = makeEmbed(User::factory()->create());

    $this->get(route('embeds'))->assertOk()->assertSee('Simulador');

    $this->get(route('embeds.show', $embed))
        ->assertOk()
        ->assertSee('src="'.route('embeds.raw', $embed, false).'?v=', false)
        ->assertDontSee('<textarea', false);
});

test('guests cannot create embeds', function () {
    Livewire::test('pages::embeds.panel')->set('title', 'Nope')->call('create')->assertForbidden();

    expect(Embed::count())->toBe(0);
});

test('users create embeds with unique slugs', function () {
    $user = User::factory()->create();
    makeEmbed($user);

    Livewire::actingAs($user)->test('pages::embeds.panel')
        ->set('title', 'Simulador')
        ->call('create')
        ->assertRedirect(route('embeds.show', 'simulador-2'));
});

test('the owner can edit an embed and the slug survives a rename', function () {
    $user = User::factory()->create();
    $embed = makeEmbed($user);

    Livewire::actingAs($user)->test('pages::embeds.show', ['slug' => $embed->slug])
        ->set('title', 'Otro nombre')
        ->set('html', '<p>nuevo</p>')
        ->call('save')
        ->assertHasNoErrors();

    expect($embed->fresh())
        ->title->toBe('Otro nombre')
        ->html->toBe('<p>nuevo</p>')
        ->slug->toBe('simulador');
});

test('other users cannot edit or delete an embed', function () {
    $embed = makeEmbed(User::factory()->create());

    $this->actingAs(User::factory()->create());

    Livewire::test('pages::embeds.show', ['slug' => $embed->slug])
        ->assertSet('html', '')
        ->set('html', '<p>hack</p>')
        ->call('save')
        ->assertForbidden();

    Livewire::test('pages::embeds.show', ['slug' => $embed->slug])->call('delete')->assertForbidden();

    expect($embed->fresh())->not->toBeNull()->html->not->toBe('<p>hack</p>');
});

test('notes render {{embed:slug}} as a sandboxed iframe', function () {
    $embed = makeEmbed(User::factory()->create(), 'Sim #1');

    $html = app(MarkdownService::class)->toHtml("Intro\n\n{{embed:{$embed->slug}}}\n\n{{embed:missing}}");

    expect($html)
        ->toContain('<iframe data-embed src="'.route('embeds.raw', $embed, false))
        ->toContain('sandbox="'.EmbedService::SANDBOX.'"')
        ->toContain('title="Sim #1"')
        ->toContain('{{embed:missing}}');
});
