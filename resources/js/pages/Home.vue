<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { login, register } from '@/routes';
import { Calendar, ShieldCheck, Ticket } from 'lucide-vue-next';
</script>

<template>
    <Head title="Welcome" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-background p-6">
        <Card class="w-full max-w-md">
            <CardContent class="flex flex-col items-center gap-6 p-8">
                <div class="flex items-center justify-center rounded-xl bg-primary p-3">
                    <AppLogoIcon class="size-10 text-primary-foreground" />
                </div>

                <div class="text-center">
                    <h1 class="text-2xl font-bold tracking-tight">Event Ticketing Portal</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Discover events, reserve tickets, and manage your bookings all in one place.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-3 text-sm text-muted-foreground">
                    <div class="flex items-center gap-3">
                        <Calendar class="size-4 shrink-0 text-primary" />
                        <span>Browse upcoming events and festivals</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <Ticket class="size-4 shrink-0 text-primary" />
                        <span>Reserve tickets with a 10-minute hold</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <ShieldCheck class="size-4 shrink-0 text-primary" />
                        <span>Secure checkout with order history</span>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-3">
                    <Button as-child class="w-full">
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child variant="outline" class="w-full">
                        <Link :href="register()">Create an account</Link>
                    </Button>
                </div>

                <p class="text-xs text-muted-foreground">
                    <Link href="/events" class="underline underline-offset-4 hover:text-foreground">
                        Browse events as guest
                    </Link>
                </p>
            </CardContent>
        </Card>
    </div>
</template>
