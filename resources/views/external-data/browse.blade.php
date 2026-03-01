@extends('layouts.app')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'External Resources', 'url' => route('external-data.browse')], ['label' => 'Browse Data']]" />

    <div class="space-y-6" x-data="externalDataBrowser()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    External Data Browser
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Browse and import data from umapyoi.net community database
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-3">
                <button @click="retryAll()" x-show="errors.characters || errors.supportCards || errors.skills || errors.news"
                    class="btn btn-warning" :disabled="loading">
                    <svg class="w-5 h-5 mr-2" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="loading ? 'Retrying...' : 'Retry All Failed'"></span>
                </button>
                <button @click="loadData()" class="btn btn-secondary" :disabled="loading">
                    <svg class="w-5 h-5 mr-2" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="loading ? 'Loading...' : 'Refresh Data'"></span>
                </button>
            </div>
        </div>

        <!-- API Status Banner -->
        <div x-show="!apiAvailable"
            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        External API Unavailable
                    </h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        The umapyoi.net API is currently unavailable. Showing cached data if available.
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'characters'"
                    :class="activeTab === 'characters' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span>Characters</span>
                    <span x-show="characters.length > 0"
                        class="py-0.5 px-2 rounded-full text-xs font-medium bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300"
                        x-text="characters.length"></span>
                    <!-- Data Source Badge (3.3.1.1) -->
                    <template x-if="shouldShowDataSourceBadge('characters')">
                        <span :class="getDataSourceBadge(dataSource.characters)?.classes"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                            <span x-text="getDataSourceBadge(dataSource.characters)?.text"></span>
                        </span>
                    </template>
                </button>
                <button @click="activeTab = 'support-cards'"
                    :class="activeTab === 'support-cards' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span>Support Cards</span>
                    <span x-show="supportCards.length > 0"
                        class="py-0.5 px-2 rounded-full text-xs font-medium bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300"
                        x-text="supportCards.length"></span>
                    <!-- Data Source Badge (3.3.1.1) -->
                    <template x-if="shouldShowDataSourceBadge('supportCards')">
                        <span :class="getDataSourceBadge(dataSource.supportCards)?.classes"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                            <span x-text="getDataSourceBadge(dataSource.supportCards)?.text"></span>
                        </span>
                    </template>
                </button>
                <button @click="activeTab = 'skills'"
                    :class="activeTab === 'skills' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span>Skills</span>
                    <span x-show="skills.length > 0"
                        class="py-0.5 px-2 rounded-full text-xs font-medium bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300"
                        x-text="skills.length"></span>
                    <!-- Data Source Badge (3.3.1.3) -->
                    <template x-if="shouldShowDataSourceBadge('skills')">
                        <span :class="getDataSourceBadge(dataSource.skills)?.classes"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                            <span x-text="getDataSourceBadge(dataSource.skills)?.text"></span>
                        </span>
                    </template>
                </button>
                <button @click="activeTab = 'news'"
                    :class="activeTab === 'news' ? 'border-primary-500 text-primary-600 dark:text-primary-400' :
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                    <span>News & Updates</span>
                    <span x-show="news.length > 0"
                        class="py-0.5 px-2 rounded-full text-xs font-medium bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300"
                        x-text="news.length"></span>
                    <!-- Data Source Badge (3.3.1.1) -->
                    <template x-if="shouldShowDataSourceBadge('news')">
                        <span :class="getDataSourceBadge(dataSource.news)?.classes"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                            <span x-text="getDataSourceBadge(dataSource.news)?.text"></span>
                        </span>
                    </template>
                </button>
            </nav>
        </div>

        <!-- Characters Tab -->
        <div x-show="activeTab === 'characters'" class="space-y-4">
            <!-- Error Banner for Characters -->
            <div x-show="errors.characters" x-transition
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-400 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Failed to load characters
                            </h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300" x-text="errors.characters"></p>
                        </div>
                    </div>
                    <button @click="retryEndpoint('characters')" :disabled="loadingCharacters"
                        class="ml-3 shrink-0 px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loadingCharacters }" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="loadingCharacters ? 'Retrying...' : 'Retry'"></span>
                    </button>
                </div>
            </div>

            <!-- Section Loading Indicator -->
            <div x-show="loadingCharacters && !loading" x-transition class="text-center py-8">
                <svg class="animate-spin h-8 w-8 mx-auto text-primary-600 dark:text-primary-400" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Loading characters...</p>
            </div>

            <!-- Search and Filters -->
            <div class="space-y-3">
                <!-- Search Bar -->
                <div class="relative rounded-md shadow-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchTerm" @input="filterData()"
                        class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Search characters...">
                </div>

                <!-- Filter Controls -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Category Filter -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category:</label>
                        <select x-model="filters.category" @change="filterData()"
                            class="form-select rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Categories</option>
                            <template x-for="category in getUniqueCategories()" :key="category">
                                <option :value="category" x-text="category"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort:</label>
                        <select x-model="sortBy" @change="filterData()"
                            class="form-select rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="id-asc">ID (Low to High)</option>
                            <option value="id-desc">ID (High to Low)</option>
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button @click="clearFilters()" x-show="hasActiveFilters()"
                        class="ml-auto text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filters
                    </button>
                </div>

                <!-- Active Filters Summary -->
                <div x-show="hasActiveFilters()" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">Active filters:</span>
                    <span x-text="getActiveFiltersCount() + ' filter(s) applied'"></span>
                    <span class="text-gray-400">•</span>
                    <span x-text="filteredCharacters.length + ' of ' + characters.length + ' characters shown'"></span>
                </div>
            </div>

            <!-- Characters Grid -->
            <div x-show="filteredCharacters.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="character in filteredCharacters" :key="character.id">
                    <div class="glass-card-alt rounded-lg p-4 hover:shadow-md transition-all group">
                        <div class="flex items-start gap-3">
                            <img :src="character.thumb_img || '/images/app_logo/logo.svg'" :alt="character.name_en"
                                class="w-16 h-16 rounded-lg object-cover cursor-pointer"
                                loading="lazy" decoding="async"
                                @click="showCharacterDetail(character)">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate cursor-pointer hover:text-primary-600 dark:hover:text-primary-400"
                                    x-text="character.name_en" @click="showCharacterDetail(character)"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="character.name_jp">
                                </p>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :style="'background-color: ' + character.color_main + '20; color: ' + character
                                            .color_main"
                                        x-text="character.category_label_en"></span>
                                    <button @click="useForCharacterCreation(character)"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity text-xs px-2 py-1 bg-primary-600 hover:bg-primary-700 text-white rounded-md flex items-center gap-1"
                                        title="Use for Character Creation">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Use
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredCharacters.length === 0 && !loading"
                class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No characters found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try a different search term or load data from the
                    API.</p>
            </div>
        </div>

        <!-- Support Cards Tab -->
        <div x-show="activeTab === 'support-cards'" class="space-y-4">
            <!-- Error Banner for Support Cards -->
            <div x-show="errors.supportCards" x-transition
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-400 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Failed to load support cards
                            </h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300" x-text="errors.supportCards"></p>
                        </div>
                    </div>
                    <button @click="retryEndpoint('supportCards')" :disabled="loadingSupportCards"
                        class="ml-3 shrink-0 px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loadingSupportCards }" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="loadingSupportCards ? 'Retrying...' : 'Retry'"></span>
                    </button>
                </div>
            </div>

            <!-- Section Loading Indicator -->
            <div x-show="loadingSupportCards && !loading" x-transition class="text-center py-8">
                <svg class="animate-spin h-8 w-8 mx-auto text-primary-600 dark:text-primary-400" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Loading support cards...</p>
            </div>

            <!-- Search and Filters -->
            <div class="space-y-3">
                <!-- Search Bar -->
                <div class="relative rounded-md shadow-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchTerm" @input="filterData()"
                        class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Search support cards...">
                </div>

                <!-- Filter Controls -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Rarity Filter -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Rarity:</label>
                        <div class="flex gap-2">
                            <button @click="toggleFilter('rarity', 'SSR')"
                                :class="filters.rarity.includes('SSR') ?
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 ring-2 ring-yellow-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-yellow-400">
                                SSR
                            </button>
                            <button @click="toggleFilter('rarity', 'SR')"
                                :class="filters.rarity.includes('SR') ?
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 ring-2 ring-purple-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-purple-400">
                                SR
                            </button>
                            <button @click="toggleFilter('rarity', 'R')"
                                :class="filters.rarity.includes('R') ?
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ring-2 ring-blue-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-blue-400">
                                R
                            </button>
                        </div>
                    </div>

                    <!-- Import Status Filter -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                        <div class="flex gap-2">
                            <button @click="toggleFilter('importStatus', 'imported')"
                                :class="filters.importStatus.includes('imported') ?
                                    'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 ring-2 ring-green-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-green-400">
                                Imported
                            </button>
                            <button @click="toggleFilter('importStatus', 'not-imported')"
                                :class="filters.importStatus.includes('not-imported') ?
                                    'bg-gray-200 text-gray-800 dark:bg-gray-600 dark:text-gray-200 ring-2 ring-gray-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-gray-400">
                                Not Imported
                            </button>
                        </div>
                    </div>

                    <!-- Sort By -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort:</label>
                        <select x-model="sortBy" @change="filterData()"
                            class="form-select rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="id-asc">ID (Low to High)</option>
                            <option value="id-desc">ID (High to Low)</option>
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="rarity-desc">Rarity (High to Low)</option>
                            <option value="rarity-asc">Rarity (Low to High)</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button @click="clearFilters()" x-show="hasActiveFilters()"
                        class="ml-auto text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filters
                    </button>
                </div>

                <!-- Active Filters Summary -->
                <div x-show="hasActiveFilters()" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">Active filters:</span>
                    <span x-text="getActiveFiltersCount() + ' filter(s) applied'"></span>
                    <span class="text-gray-400">•</span>
                    <span x-text="filteredSupportCards.length + ' of ' + supportCards.length + ' cards shown'"></span>
                </div>
            </div>

            <!-- Support Cards Grid -->
            <div x-show="filteredSupportCards.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="card in filteredSupportCards" :key="card.id">
                    <div class="glass-card-alt rounded-lg p-4 hover:shadow-md transition-all group">
                        <div class="flex items-start gap-3">
                            <img :src="getSupportCardImage(card.id)" :alt="card.title_en || 'Support Card'"
                                class="w-16 h-16 rounded-lg object-cover cursor-pointer"
                                loading="lazy" decoding="async"
                                x-on:error="$event.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(card.title_en || card.name_en || 'Card') + '&background=random&color=fff'"
                                @click="showSupportCardDetail(card)">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2 cursor-pointer hover:text-primary-600 dark:hover:text-primary-400"
                                    x-text="card.title_en || card.name_en || 'Unknown'"
                                    @click="showSupportCardDetail(card)"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                    x-text="formatCharacterName(card.gametora)"></p>
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': card
                                                    .rarity === 'SSR',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': card
                                                    .rarity === 'SR',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': card
                                                    .rarity === 'R'
                                            }"
                                            x-text="card.rarity || 'R'"></span>
                                        <span x-show="card.gametora" class="text-xs text-gray-500 dark:text-gray-400">
                                            #<span x-text="card.id"></span>
                                        </span>
                                    </div>
                                    <button @click="importSupportCard(card)"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity text-xs px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded-md flex items-center gap-1"
                                        title="Import to Collection">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Import
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredSupportCards.length === 0 && !loading"
                class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No support cards found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try a different search term or load data from the
                    API.</p>
            </div>
        </div>

        <!-- Skills Tab -->
        <div x-show="activeTab === 'skills'" class="space-y-4">
            <!-- Error Banner for Skills -->
            <div x-show="errors.skills" x-transition
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-400 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Failed to load skills
                            </h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300" x-text="errors.skills"></p>
                        </div>
                    </div>
                    <button @click="retryEndpoint('skills')" :disabled="loadingSkills"
                        class="ml-3 shrink-0 px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loadingSkills }" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="loadingSkills ? 'Retrying...' : 'Retry'"></span>
                    </button>
                </div>
            </div>

            <!-- Section Loading Indicator -->
            <div x-show="loadingSkills && !loading" x-transition class="text-center py-8">
                <svg class="animate-spin h-8 w-8 mx-auto text-primary-600 dark:text-primary-400" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Loading skills...</p>
            </div>

            <!-- Info Banner -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-300">
                        <p><strong>Note:</strong> Skills data is sourced from the local database. The umapyoi.net API does
                            not currently provide a skills endpoint.</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="space-y-3">
                <!-- Search Bar -->
                <div class="relative rounded-md shadow-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchTerm" @input="filterData()"
                        class="form-input block w-full rounded-md border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Search skills...">
                </div>

                <!-- Filter Controls -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Rarity Filter -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Rarity:</label>
                        <div class="flex gap-2">
                            <button @click="toggleFilter('skillRarity', 'unique')"
                                :class="filters.skillRarity.includes('unique') ?
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 ring-2 ring-yellow-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-yellow-400">
                                Unique
                            </button>
                            <button @click="toggleFilter('skillRarity', 'rare')"
                                :class="filters.skillRarity.includes('rare') ?
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 ring-2 ring-purple-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-purple-400">
                                Rare
                            </button>
                            <button @click="toggleFilter('skillRarity', 'normal')"
                                :class="filters.skillRarity.includes('normal') ?
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ring-2 ring-blue-500' :
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                                class="px-3 py-1 rounded-md text-xs font-medium transition-all hover:ring-2 hover:ring-blue-400">
                                Normal
                            </button>
                        </div>
                    </div>

                    <!-- Type Filter -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type:</label>
                        <select x-model="filters.skillType" @change="filterData()"
                            class="form-select rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">All Types</option>
                            <template x-for="type in getUniqueSkillTypes()" :key="type">
                                <option :value="type" x-text="type"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort:</label>
                        <select x-model="sortBy" @change="filterData()"
                            class="form-select rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="id-asc">ID (Low to High)</option>
                            <option value="id-desc">ID (High to Low)</option>
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="rarity-desc">Rarity (High to Low)</option>
                            <option value="rarity-asc">Rarity (Low to High)</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <button @click="clearFilters()" x-show="hasActiveFilters()"
                        class="ml-auto text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filters
                    </button>
                </div>

                <!-- Active Filters Summary -->
                <div x-show="hasActiveFilters()" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">Active filters:</span>
                    <span x-text="getActiveFiltersCount() + ' filter(s) applied'"></span>
                    <span class="text-gray-400">•</span>
                    <span x-text="filteredSkills.length + ' of ' + skills.length + ' skills shown'"></span>
                </div>
            </div>

            <!-- Skills Grid -->
            <div x-show="filteredSkills.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="skill in filteredSkills" :key="skill.id">
                    <div class="glass-card-alt rounded-lg p-4 hover:shadow-md transition-all group">
                        <div class="flex items-start gap-3">
                            <!-- Skill Type Icon -->
                            <div class="shrink-0 w-12 h-12 rounded-lg flex items-center justify-center text-2xl"
                                :class="getSkillTypeClasses(skill.type)">
                                <span x-text="getSkillTypeIcon(skill.type)"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-2"
                                        x-text="skill.name || skill.name_en"></h3>
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': skill
                                                .rarity === 'unique',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': skill
                                                .rarity === 'rare',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': !skill
                                                .rarity || skill.rarity === 'normal'
                                        }"
                                        x-text="skill.rarity || 'normal'"></span>
                                </div>
                                <!-- Skill Type & SP Cost -->
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 capitalize"
                                        x-text="skill.type || 'normal'"></span>
                                    <span x-show="skill.sp_cost"
                                        class="text-xs font-medium text-primary-600 dark:text-primary-400">
                                        <span x-text="skill.sp_cost"></span> SP
                                    </span>
                                </div>
                                <!-- Description -->
                                <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 mt-2"
                                    x-text="truncateText(skill.description || 'No description available', 100)">
                                </p>
                                <!-- Additional Info Row -->
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    <!-- Evolution Badge -->
                                    <span x-show="skill.can_evolve"
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                        </svg>
                                        Evolves
                                    </span>
                                    <!-- Evolved Badge -->
                                    <span x-show="skill.is_evolution"
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Evolved
                                    </span>
                                    <!-- Meta Tier Badge -->
                                    <span x-show="skill.meta_tier"
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs"
                                        :class="{
                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': skill
                                                .meta_tier === 'S' || skill.meta_tier === 'SS',
                                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': skill
                                                .meta_tier === 'A',
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': skill
                                                .meta_tier === 'B',
                                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400': skill
                                                .meta_tier === 'C' || skill.meta_tier === 'D'
                                        }">
                                        Tier <span x-text="skill.meta_tier"></span>
                                    </span>
                                    <!-- Internal ID -->
                                    <span x-show="skill.internal_id" class="text-xs text-gray-500 dark:text-gray-400">
                                        #<span x-text="skill.internal_id"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredSkills.length === 0 && !loading"
                class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No skills found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try a different search term or load data from the
                    API.</p>
            </div>
        </div>

        <!-- News Tab -->
        <div x-show="activeTab === 'news'" class="space-y-4">
            <!-- Error Banner for News -->
            <div x-show="errors.news" x-transition
                class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-red-400 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Failed to load news
                            </h3>
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300" x-text="errors.news"></p>
                        </div>
                    </div>
                    <button @click="retryEndpoint('news')" :disabled="loadingNews"
                        class="ml-3 shrink-0 px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': loadingNews }" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="loadingNews ? 'Retrying...' : 'Retry'"></span>
                    </button>
                </div>
            </div>

            <!-- Section Loading Indicator -->
            <div x-show="loadingNews && !loading" x-transition class="text-center py-8">
                <svg class="animate-spin h-8 w-8 mx-auto text-primary-600 dark:text-primary-400" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Loading news...</p>
            </div>

            <div x-show="news.length > 0" class="space-y-3">
                <template x-for="item in news" :key="item.id">
                    <div class="glass-card-alt rounded-lg p-4 hover:shadow-md transition-all">
                        <div class="flex items-start gap-4">
                            <div x-show="item.thumb_img" class="shrink-0">
                                <img :src="item.thumb_img" :alt="item.title_en"
                                    class="w-24 h-24 rounded-lg object-cover" loading="lazy" decoding="async">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
                                        x-text="item.category || 'News'"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400"
                                        x-text="item.published_at ? new Date(item.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'Unknown date'"></span>
                                </div>
                                <div class="text-sm text-gray-900 dark:text-white line-clamp-3"
                                    x-text="truncateText(stripHtml(item.title_en || item.title || 'No content'), 200)">
                                </div>
                                <p x-show="item.title_jp"
                                    class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2"
                                    x-text="truncateText(item.title_jp, 100)"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="news.length === 0 && !loading" class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No news available</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click "Refresh Data" to load the latest news.</p>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="text-center py-12">
            <svg class="animate-spin h-12 w-12 mx-auto text-primary-600 dark:text-primary-400" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Loading data from umapyoi.net...</p>
        </div>

        <!-- Character Detail Modal -->
        <div x-show="showCharacterModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75"
                    @click="showCharacterModal = false"></div>
                <div
                    class="relative inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all">
                    <div class="absolute top-4 right-4">
                        <button @click="showCharacterModal = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <template x-if="selectedCharacter">
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <img :src="selectedCharacter.thumb_img || '/images/app_logo/logo.svg'"
                                    :alt="selectedCharacter.name_en" class="w-24 h-24 rounded-xl object-cover" loading="lazy" decoding="async">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white"
                                        x-text="selectedCharacter.name_en"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="selectedCharacter.name_jp"></p>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                            :style="'background-color: ' + selectedCharacter.color_main + '20; color: ' +
                                                selectedCharacter.color_main"
                                            x-text="selectedCharacter.category_label_en"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Character Info</h4>
                                <dl class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">External ID</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white"
                                            x-text="selectedCharacter.id"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Source</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">umapyoi.net</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex gap-3">
                                <button @click="useForCharacterCreation(selectedCharacter); showCharacterModal = false;"
                                    class="flex-1 btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Use for Character Creation
                                </button>
                                <button @click="showCharacterModal = false" class="btn btn-secondary">
                                    Close
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Support Card Detail Modal -->
        <div x-show="showSupportCardModal" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75"
                    @click="showSupportCardModal = false"></div>
                <div
                    class="relative inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all">
                    <div class="absolute top-4 right-4">
                        <button @click="showSupportCardModal = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <template x-if="selectedSupportCard">
                        <div class="space-y-4">
                            <div class="flex items-start gap-4">
                                <img :src="getSupportCardImage(selectedSupportCard.id)"
                                    :alt="selectedSupportCard.title_en" class="w-24 h-24 rounded-xl object-cover"
                                    loading="lazy" decoding="async"
                                    x-on:error="$event.target.src = '/images/app_logo/logo.svg'">
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white"
                                        x-text="selectedSupportCard.title_en || 'Unknown'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="formatCharacterName(selectedSupportCard.gametora)"></p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': selectedSupportCard
                                                    .rarity === 'SSR',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': selectedSupportCard
                                                    .rarity === 'SR',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': selectedSupportCard
                                                    .rarity === 'R'
                                            }"
                                            x-text="selectedSupportCard.rarity || 'R'"></span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">#<span
                                                x-text="selectedSupportCard.id"></span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Card Info</h4>
                                <dl class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">External ID</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white"
                                            x-text="selectedSupportCard.id"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500 dark:text-gray-400">Character ID</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white"
                                            x-text="selectedSupportCard.chara_id || 'N/A'"></dd>
                                    </div>
                                    <div x-show="selectedSupportCard.gametora">
                                        <dt class="text-gray-500 dark:text-gray-400">GameTora</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">
                                            <a :href="'https://gametora.com/umamusume/supports/' + selectedSupportCard.gametora"
                                                target="_blank"
                                                class="text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                                View on GameTora →
                                            </a>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex gap-3">
                                <button @click="importSupportCard(selectedSupportCard); showSupportCardModal = false;"
                                    class="flex-1 btn btn-primary" :disabled="importingCard">
                                    <svg x-show="!importingCard" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <svg x-show="importingCard" class="w-4 h-4 mr-2 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span x-text="importingCard ? 'Importing...' : 'Import to Collection'"></span>
                                </button>
                                <button @click="showSupportCardModal = false" class="btn btn-secondary">
                                    Close
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-4 right-4 z-50" style="display: none;">
            <div class="rounded-lg shadow-lg p-4 max-w-sm"
                :class="toast.type === 'success' ?
                    'bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800' :
                    'bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800'">
                <div class="flex items-start gap-3">
                    <svg x-show="toast.type === 'success'" class="w-5 h-5 text-green-500" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-5 h-5 text-red-500" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium"
                            :class="toast.type === 'success' ? 'text-green-800 dark:text-green-200' :
                                'text-red-800 dark:text-red-200'"
                            x-text="toast.message"></p>
                    </div>
                    <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/pages/external-data/browse.js'])
@endsection
