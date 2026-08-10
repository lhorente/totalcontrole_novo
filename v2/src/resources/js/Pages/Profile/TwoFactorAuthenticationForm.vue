<template>
    <jet-action-section>
        <template #title>
            Autenticação em Dois Fatores
        </template>

        <template #description>
            A autenticação em dois fatores é obrigatória para acessar o sistema.
        </template>

        <template #content>
            <h3 class="text-lg font-medium text-gray-900" v-if="twoFactorConfirmed">
                A autenticação em dois fatores está ativa.
            </h3>

            <h3 class="text-lg font-medium text-red-600" v-else>
                Você precisa ativar a autenticação em dois fatores para continuar usando o sistema.
            </h3>

            <div class="mt-3 max-w-xl text-sm text-gray-600">
                <p>
                    Com a autenticação em dois fatores ativa, você precisará informar um código gerado por um aplicativo autenticador (ex: Google Authenticator) a cada novo login.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold" v-if="! twoFactorConfirmed">
                            Escaneie o QR code abaixo com o aplicativo autenticador do seu celular e informe o código gerado para confirmar a ativação.
                        </p>
                        <p class="font-semibold" v-else>
                            Escaneie o QR code abaixo com o aplicativo autenticador do seu celular.
                        </p>
                    </div>

                    <div class="mt-4" v-html="qrCode">
                    </div>
                </div>

                <div v-if="! twoFactorConfirmed" class="mt-4 max-w-xl">
                    <jet-label for="code" value="Código do aplicativo autenticador" />
                    <jet-input id="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                                    class="mt-1 block w-full" v-model="confirmationForm.code" autofocus />
                    <jet-input-error :message="confirmationForm.error('code')" class="mt-2" />
                </div>

                <div v-if="recoveryCodes.length > 0">
                    <div class="mt-4 max-w-xl text-sm text-gray-600">
                        <p class="font-semibold">
                            Guarde estes códigos de recuperação em um gerenciador de senhas seguro. Eles são a única forma de recuperar o acesso caso você perca o dispositivo com o aplicativo autenticador — não há recuperação por e-mail ou SMS.
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                        <div v-for="code in recoveryCodes">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div v-if="! twoFactorEnabled">
                    <jet-confirms-password @confirmed="enableTwoFactorAuthentication">
                        <jet-button type="button" :class="{ 'opacity-25': enabling }" :disabled="enabling">
                            Ativar
                        </jet-button>
                    </jet-confirms-password>
                </div>

                <div v-else-if="! twoFactorConfirmed">
                    <jet-confirms-password @confirmed="confirmTwoFactorAuthentication">
                        <jet-button type="button" :class="{ 'opacity-25': confirmationForm.processing }"
                                        :disabled="confirmationForm.processing">
                            Confirmar
                        </jet-button>
                    </jet-confirms-password>
                </div>

                <div v-else>
                    <jet-confirms-password @confirmed="regenerateRecoveryCodes">
                        <jet-secondary-button class="mr-3"
                                        v-if="recoveryCodes.length > 0">
                            Gerar Novos Códigos de Recuperação
                        </jet-secondary-button>
                    </jet-confirms-password>

                    <jet-confirms-password @confirmed="showRecoveryCodes">
                        <jet-secondary-button class="mr-3" v-if="recoveryCodes.length == 0">
                            Exibir Códigos de Recuperação
                        </jet-secondary-button>
                    </jet-confirms-password>
                </div>
            </div>
        </template>
    </jet-action-section>
</template>

<script>
    import JetActionSection from './../../Jetstream/ActionSection'
    import JetButton from './../../Jetstream/Button'
    import JetConfirmsPassword from './../../Jetstream/ConfirmsPassword'
    import JetInput from './../../Jetstream/Input'
    import JetInputError from './../../Jetstream/InputError'
    import JetLabel from './../../Jetstream/Label'
    import JetSecondaryButton from './../../Jetstream/SecondaryButton'

    export default {
        components: {
            JetActionSection,
            JetButton,
            JetConfirmsPassword,
            JetInput,
            JetInputError,
            JetLabel,
            JetSecondaryButton,
        },

        data() {
            return {
                enabling: false,

                qrCode: null,
                recoveryCodes: [],

                confirmationForm: this.$inertia.form({
                    code: '',
                }, {
                    bag: 'confirmTwoFactorAuthentication',
                }),
            }
        },

        created() {
            if (this.twoFactorEnabled && ! this.twoFactorConfirmed) {
                this.showQrCode()
            }
        },

        methods: {
            enableTwoFactorAuthentication() {
                this.enabling = true

                this.$inertia.post('/user/two-factor-authentication', {}, {
                    preserveScroll: true,
                }).then(() => {
                    return this.showQrCode()
                }).then(() => {
                    this.enabling = false
                })
            },

            showQrCode() {
                return axios.get('/user/two-factor-qr-code')
                        .then(response => {
                            this.qrCode = response.data.svg
                        })
            },

            confirmTwoFactorAuthentication() {
                this.confirmationForm.post('/user/confirmed-two-factor-authentication', {
                    preserveScroll: true,
                }).then(() => {
                    if (! this.confirmationForm.hasErrors()) {
                        this.qrCode = null
                        this.showRecoveryCodes()
                    }
                })
            },

            showRecoveryCodes() {
                return axios.get('/user/two-factor-recovery-codes')
                        .then(response => {
                            this.recoveryCodes = response.data
                        })
            },

            regenerateRecoveryCodes() {
                axios.post('/user/two-factor-recovery-codes')
                        .then(response => {
                            this.showRecoveryCodes()
                        })
            },
        },

        computed: {
            twoFactorEnabled() {
                return ! this.enabling && this.$page.user.two_factor_enabled
            },

            twoFactorConfirmed() {
                return this.twoFactorEnabled && !! this.$page.user.two_factor_confirmed_at
            },
        }
    }
</script>
