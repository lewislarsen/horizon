<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAipJREFUeNrEV8txwjAQtQ2HHCmB3JKbSQOYCoA0gD0pgFBBwpEToQAGKmDglpwgFdg5kZtNB1BBsuusZ4RY2ZZjYGd2jGWh97Q/rUwjpziPT3V4dECboDZoXZoSka5Al5vFNMqzrpkD2IFHn8B1ZAM6BCKbQgQAuAaPWQFgjoinsoipAEcTr0FrRjmyJxLLTAI5wXFXAehBGMPYcDKIIIm5kkAGOJpwAjqHRfYpbkOXvTBBypIwpT+HCvA3Cqi9Rta8EhHOHS1YCy1oWMKHmQIcGQ90wGMfLaZIoEGAoiDGOHmxhFTr5PGZJgncZYszEGC6ogX6nNn/Ay6RGDCfYveYVOFCJuAaumbPiIk1kyUNS2H6SZngyZrMWM+i/JVlXjK4QUVI3pRTpYPlaG6yeyGvm0Jef1ItiArwQBKu8G5bTMEIhKLkU3q65D+HgieE7+MCBHbygMVMOlCK+CnVDOUZ5s00ghCt2T45C+DDD2MBW/O066YFLYGvuXU5C9i6GYaLUzqr+olQtS5aIMwwtW6QfQnv7awNVanolEWgo9nABBb1cNeSmMDyigRWZkqdPrdEkDm3SRYMr7D7odwRXdIK8e7lOuAxh8W5pHtSiOhw8S4A7iX9IErlyC5b/7t+/7Ar4TKiEuyyRuJA5cQ5Wz8gEhgPNyXvfCQPVtgI+SPxAT/vSqiSEbXh70Uvp27GRSMNeJjV2Jp5V6MGpUeuUR0wAemKuwdy8ivAAJcc0R2NFxWtAAAAAElFTkSuQmCC">

    <title>Horizon{{ config('horizon.name') ? ' - ' . config('horizon.name') : '' }}</title>

    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    {{ Laravel\Horizon\Horizon::css() }}

    <style data-scheme="dark" media="max-width: 1px">
        :root {
            color-scheme: dark;
        }
    </style>

    {{ Laravel\Horizon\Horizon::js() }}
</head>
<body class="w-full lg:min-w-[1024px] h-svh overflow-y-clip font-sans antialiased text-default text-sm">
<div id="horizon" v-cloak>
    <alert :message="alert.message"
           :type="alert.type"
           :auto-close="alert.autoClose"
           :confirmation-proceed="alert.confirmationProceed"
           :confirmation-cancel="alert.confirmationCancel"
           v-if="alert.type"></alert>

    <div id="scroll-parent" class="flex h-svh w-full transition-all duration-200 ease-out bg-stronger">
        <div id="scroll-container" class="flex h-svh w-full flex-col overflow-y-scroll [scrollbar-gutter:stable]" scroll-region="true">
            <div class="relative flex w-full flex-1 flex-col justify-center">

                <div class="sticky top-0 z-30">
                    <div class="bg-app-background rounded-t-lg">
                        <header class="mx-auto max-w-[1920px] flex-col px-4 sm:px-8">
                            <div class="flex items-center">
                                <div class="min-w-0 flex-1">
                                    <nav class="flex h-16.5 flex-1 items-center gap-5">
                                        <router-link to="/" class="focus-visible:shadow-xs-selected hidden rounded-md text-[var(--background-color-stronger)] focus:outline-none sm:block">
                                            <svg viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-7">
                                                <path class="fill-primary" d="M5.26176342 26.4094389C2.04147988 23.6582233 0 19.5675182 0 15c0-4.1421356 1.67893219-7.89213562 4.39339828-10.60660172C7.10786438 1.67893219 10.8578644 0 15 0c8.2842712 0 15 6.71572875 15 15 0 8.2842712-6.7157288 15-15 15-3.716753 0-7.11777662-1.3517984-9.73823658-3.5905611zM4.03811305 15.9222506C5.70084247 14.4569342 6.87195416 12.5 10 12.5c5 0 5 5 10 5 3.1280454 0 4.2991572-1.9569336 5.961887-3.4222502C25.4934253 8.43417206 20.7645408 4 15 4 8.92486775 4 4 8.92486775 4 15c0 .3105915.01287248.6181765.03811305.9222506z" fill="currentColor"/>
                                            </svg>
                                            <span class="sr-only">Go to dashboard</span>
                                        </router-link>

                                        <div class="-ml-1 flex items-center gap-1.5 sm:ml-0">
                                            <nav aria-label="breadcrumb" class="flex items-center gap-x-1.5">
                                                <div class="flex text-sm">
                                                    <router-link to="/" class="group relative text-strong font-medium leading-5 flex gap-x-2 rounded-md py-1 pr-2 pl-2 hover:bg-hovered focus:outline-none focus-visible:bg-default focus-visible:shadow-xs-selected">
                                                        <span class="max-w-40 truncate sm:max-w-60"><strong>Laravel</strong> Horizon</span>
                                                        <span aria-hidden="true" class="pointer-events-auto absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 pointer-fine:hidden"></span>
                                                    </router-link>
                                                </div>
                                            </nav>
                                        </div>

                                        <div aria-hidden="true" class="-ml-4 flex-1"></div>

                                        <div class="flex items-center gap-5">
                                            <div class="hidden items-center sm:flex">
                                                <a href="https://laravel.com/docs/horizon" target="_blank" rel="noopener" class="h-8 relative items-center rounded-md px-3 text-weak hover:bg-weak hover:text-strong focus:outline-none focus-visible:shadow-xs-selected active:bg-strong inline-flex">
                                                    Docs
                                                    <span aria-hidden="true" class="pointer-events-auto absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 pointer-fine:hidden"></span>
                                                </a>
                                            </div>

                                            <scheme-toggler></scheme-toggler>

                                            <button class="relative content-center items-center rounded-md p-2 text-sm font-medium w-fit h-8 text-icon-alpha hover:bg-stronger/5 dark:hover:bg-white/10 hover:shadow-xs-hovered hover:text-icon-alpha-hovered focus-visible:bg-weak focus:outline-none focus-visible:shadow-xs-active flex transition-colors"
                                                    v-on:click.prevent="autoLoadNewEntries"
                                                    title="Auto Load New Entries">
                                                <span class="size-5 shrink-0 relative">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span v-if="autoLoadsNewEntries" class="absolute -top-0.5 -right-0.5 flex size-2">
                                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                                        <span class="relative inline-flex rounded-full size-2 bg-success"></span>
                                                    </span>
                                                </span>
                                                <span aria-hidden="true" class="pointer-events-auto absolute top-1/2 left-1/2 size-[max(100%,2.75rem)] -translate-x-1/2 -translate-y-1/2 pointer-fine:hidden"></span>
                                            </button>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                        </header>

                        <div class="mx-auto max-w-[1920px] px-4 sm:px-8"></div>

                        <div class="border-weaker relative z-10 border-b sm:border-none">
                            <div class="mx-auto w-full max-w-[1920px] overflow-x-auto px-4 sm:px-8"></div>
                        </div>
                    </div>

                    <div class="pointer-events-none absolute inset-x-0 -bottom-8 hidden px-0 sm:block sm:px-2">
                        <div class="relative">
                            <div class="bg-app-background absolute -top-px -left-px size-4"></div>
                            <div class="bg-app-background absolute -top-px -right-px size-4"></div>
                            <div class="relative -mx-px -mt-px h-8">
                                <div class="bg-weaker border-weaker absolute inset-x-4 top-0 h-px border-t dark:border-[#191A19]"></div>
                                <div class="bg-weaker border-weaker absolute top-0 left-0 size-4 rounded-tl-none border-t border-l sm:rounded-tl-lg dark:border-[#191A19] dark:bg-[#191A19]"></div>
                                <div class="bg-weaker border-weaker absolute top-0 right-0 size-4 rounded-tr-none border-t border-r sm:rounded-tr-lg dark:border-[#191A19] dark:bg-[#191A19]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <main class="bg-app-background flex flex-1 flex-col px-0 py-px sm:px-2">
                    <div id="main-content-wrapper" class="bg-weaker ring-weaker relative mx-auto flex w-full grow flex-col items-stretch rounded-none shadow-xs ring-0 sm:rounded-lg sm:ring-1">
                        <div class="mx-auto w-full max-w-[1920px] px-4 pt-4 pb-20 sm:px-6 sm:pt-10" data-slot="main-content">
                            <div class="flex w-full flex-col gap-x-6 gap-y-4 sm:flex-row sm:items-start">

                                <div class="min-h-0 w-full shrink-0 space-y-6 sm:sticky sm:ml-auto sm:w-[192px] sm:self-start">
                                    <h2 class="text-strong -ml-1 p-1 font-medium focus-visible:shadow-xs-selected focus-visible:outline-none focus:outline-none focus-visible:rounded-md sm:text-xl/8 text-base/8 ml-3 hidden sm:block">
                                        Horizon
                                    </h2>

                                    <div class="flex flex-col gap-y-1">
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/dashboard" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Dashboard</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/monitoring" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Monitoring</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/metrics" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Metrics</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/batches" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Batches</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/jobs/pending" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Pending Jobs</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/jobs/completed" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Completed Jobs</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/jobs/silenced" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Silenced Jobs</span>
                                        </router-link>
                                        <router-link active-class="bg-hovered font-medium text-strong" to="/failed" class="px-3 py-2 rounded-md w-full hover:bg-hovered hover:text-strong inline-flex items-center gap-3">
                                            <span>Failed Jobs</span>
                                        </router-link>
                                    </div>
                                </div>

                                <div class="w-full flex flex-col shrink-0 max-w-[900px]">
                                    @if ($isDownForMaintenance)
                                        <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl mb-6">
                                            <div class="bg-default shadow-xs-with-border rounded-lg">
                                                <div class="flex items-center gap-x-3 px-5 py-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-warning shrink-0">
                                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                                    </svg>
                                                    <div>
                                                        <p class="text-strong font-medium">Maintenance Mode</p>
                                                        <p class="text-default text-xssm mt-1">This application is in "maintenance mode". Queued jobs may not be processed unless your worker is using the "force" flag.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <router-view></router-view>
                                </div>

                                <div class="mr-auto hidden w-full shrink-0 sm:block sm:w-[192px]"></div>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="bg-app-background hidden sm:block">
                    <div class="text-default mx-auto flex max-w-[1920px] flex-col items-center justify-between gap-y-4 px-6 py-4 sm:flex-row">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 16" class="text-icon size-4">
                                    <path fill="currentColor" fill-rule="evenodd" d="M4.368 1.306c.064 0 .122.02.174.053l2.46 1.414a.31.31 0 0 1 .142.154.378.378 0 0 1 .023.145v5.055L8.972 7.09V4.466c0-.068.016-.131.057-.189a.462.462 0 0 1 .15-.126l2.454-1.412a.29.29 0 0 1 .145-.042.29.29 0 0 1 .144.042l2.5 1.441a.285.285 0 0 1 .128.139c.023.054.024.11.024.151v2.801a.338.338 0 0 1-.037.169.359.359 0 0 1-.132.128l-.004.002-2.297 1.32v2.614a.353.353 0 0 1-.042.184.369.369 0 0 1-.138.127l-.002.001-4.905 2.82a.383.383 0 0 1-.184.053.383.383 0 0 1-.185-.054l-.003-.001-4.891-2.821-.014-.009a.522.522 0 0 1-.099-.075.258.258 0 0 1-.071-.187V3.03a.28.28 0 0 1 .144-.245l2.478-1.427a.35.35 0 0 1 .176-.052ZM2.563 3.06 4.364 2.02 6.17 3.06 4.364 4.1l-1.8-1.041Zm9.212 2.436L9.971 4.458l1.804-1.038 1.8 1.038-1.8 1.038ZM6.506 3.632 4.701 4.674v4.872l1.805-1.042V3.632Zm-2.47 1.042-1.81-1.039v7.691l4.28 2.462v-2.075l-2.279-1.288-.005-.004c-.034-.02-.084-.05-.12-.096a.307.307 0 0 1-.066-.202V4.674ZM9.638 7.09v-2.06l1.809 1.043v2.051L9.637 7.09Zm2.466 1.034v-2.05l1.809-1.043v2.059l-1.81 1.034ZM6.83 11.136l-1.79-1.018 4.265-2.46 1.8 1.043-4.275 2.435Zm.337 2.652v-2.076l4.28-2.446v2.06l-4.28 2.462Z" clip-rule="evenodd"/>
                                </svg>
                                <span>Laravel Horizon</span>
                            </div>
                            <span> · </span>
                            <div class="flex items-center gap-1">
                                <span class="font-serif text-base italic">Crafted by Artisans</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <a href="https://laravel.com/docs/horizon" rel="noreferrer" target="_blank" class="hover:text-link-hovered focus-visible:shadow-xs-selected rounded focus:outline-none">Docs</a>
                            <a href="https://github.com/lewislarsen/horizon" rel="noreferrer" target="_blank" class="hover:text-link-hovered focus-visible:shadow-xs-selected rounded focus:outline-none">GitHub</a>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>
</body>
</html>