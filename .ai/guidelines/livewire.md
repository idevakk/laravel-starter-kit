# Livewire Guidelines

## Component Types

### Full-Page Components (Routed)
Live in `App\Livewire\Pages\` or feature-specific directories like `App\Livewire\Auth\`.
Use the `#[Layout]` attribute to specify the layout.

```php
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    // Full-page component
}
```

### Nested Components
Live in `App\Livewire\` organized by feature. Used inside full-page components for reusable interactive sections.

## Navigation

Use `wire:navigate` on all internal navigation links. This enables SPA-like page transitions without full page reloads.

```blade
<a href="{{ route('dashboard') }}" wire:navigate>Dashboard</a>
```

## Component Conventions

1. **Validate early.** Use `#[Validate]` attributes for simple rules, `$this->validate()` for complex ones.
2. **Typed properties.** Every public property must have a type declaration.
3. **Events over tight coupling.** Use `$this->dispatch()` for communication between components.
4. **Keep components focused.** If a component does more than one thing, split it.
5. **No business logic in components.** Components handle UI state and delegate to Actions for business logic.

## Flux UI Components

Use Flux components as the default UI kit. Only create custom components when Flux genuinely cannot handle the use case. Document the justification in a code comment when creating a custom component.

```blade
{{-- Prefer this --}}
<flux:button wire:click="save">Save</flux:button>

{{-- Over hand-rolled buttons --}}
<button class="..." wire:click="save">Save</button>
```
