<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppearanceContent from '@/components/custom/UserProfile/AppearanceContent.vue';
import DeleteAccountCard from '@/components/custom/UserProfile/DeleteAccountCard.vue';
import PasswordContent from '@/components/custom/UserProfile/PasswordContent.vue';
import ProfileAvatar from '@/components/custom/UserProfile/ProfileAvatar.vue';
import ProfileContent from '@/components/custom/UserProfile/ProfileContent.vue';
import TwoFactorContent from '@/components/custom/UserProfile/TwoFactorContent.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItem, User } from '@/types';

type Props = {
    mustVerifyEmail?: boolean;
    status?: string;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    mustVerifyEmail: false,
    status: undefined,
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pannello admin',
        href: '/admin/dashboard',
    },
    {
        title: 'Profilo utente',
        href: '/admin/user/settings/profile',
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user as User);
const urls = computed(() => (page.props.urls as Record<string, string>) ?? {});

const getSettingsUrl = (
    suffix: 'profile' | 'password' | 'appearance' | 'two-factor',
): string => {
    if (page.url.includes('/admin/user/settings')) {
        return {
            profile: '/admin/user/settings/profile',
            password: '/admin/user/settings/password',
            appearance: '/admin/user/settings/appearance',
            'two-factor': '/admin/user/settings/two-factor',
        }[suffix];
    }

    return {
        profile: urls.value.settingsProfile ?? '/admin/user/settings/profile',
        password: urls.value.settingsPassword ?? '/admin/user/settings/password',
        appearance: urls.value.settingsAppearance ?? '/admin/user/settings/appearance',
        'two-factor': urls.value.settingsTwoFactor ?? '/admin/user/settings/two-factor',
    }[suffix];
};

const activeSection = computed(() => {
    if (page.url.includes('/user/settings/password') || page.url.includes('/admin/user/settings/password')) {
        return 'password';
    }

    if (page.url.includes('/user/settings/appearance') || page.url.includes('/admin/user/settings/appearance')) {
        return 'appearance';
    }

    if (page.url.includes('/user/settings/two-factor') || page.url.includes('/admin/user/settings/two-factor')) {
        return 'two-factor';
    }

    return 'profile';
});
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <Head title="Profilo utente" />

        <h1 class="sr-only">Profilo utente (amministratore)</h1>

        <SettingsLayout>
            <template v-if="activeSection === 'profile'">
                <section
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <ProfileAvatar :user="user" size-class="h-12 w-12" />

                            <div class="min-w-0">
                                <h2 class="truncate text-2xl font-semibold">
                                    Area utente admin
                                </h2>
                                <p class="truncate text-sm text-muted-foreground">
                                    {{ user.name }} · {{ user.email }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-sidebar-border/70 px-4 py-3 text-sm text-muted-foreground"
                        >
                            <span v-if="user.email_verified_at">Email verificata</span>
                            <span v-else>Email da verificare</span>
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                >
                    <ProfileContent
                        :must-verify-email="props.mustVerifyEmail"
                        :status="props.status"
                        :submit-url="getSettingsUrl('profile')"
                    />
                </section>

                <section
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                >
                    <DeleteAccountCard :delete-url="getSettingsUrl('profile')" />
                </section>
            </template>

            <template v-else-if="activeSection === 'password'">
                <section
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                >
                    <PasswordContent :submit-url="getSettingsUrl('password')" />
                </section>
            </template>

            <template v-else-if="activeSection === 'appearance'">
                <section
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                >
                    <AppearanceContent />
                </section>
            </template>

            <template v-else-if="activeSection === 'two-factor'">
                <section
                    class="rounded-xl border border-sidebar-border/70 bg-card p-4 sm:p-6"
                >
                    <TwoFactorContent
                        :requires-confirmation="props.requiresConfirmation"
                        :two-factor-enabled="props.twoFactorEnabled"
                    />
                </section>
            </template>
        </SettingsLayout>
    </AdminLayout>
</template>
