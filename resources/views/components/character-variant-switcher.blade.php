@props([
    'variants' => [],
    'defaultId' => null,
])

<label class="sr-only" :for="`variant-switcher-${character.id}`">Select character version</label>
<select
    :id="`variant-switcher-${character.id}`"
    data-testid="variant-switcher"
    class="pointer-events-auto min-h-11 rounded-md border border-neutral-300 bg-white px-2 py-2 text-xs font-medium text-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-200"
    x-model="selectedVariantId"
    @change.stop="selectVariant($event.target.value)"
    aria-label="Select character version"
>
    <template x-for="variant in variants" :key="variant.id">
        <option :value="variant.id" x-text="formatVariantLabel(variant)"></option>
    </template>
</select>
