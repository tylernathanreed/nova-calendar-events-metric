<template>
    <loading-card :loading="loading" class="px-6 py-4">
        <div class="flex mb-3">
            <h3 class="mr-3 text-base text-80 font-bold">{{ title }}</h3>

            <div v-if="helpText" class="absolute pin-r pin-b p-2 z-50">
                <tooltip trigger="click">
                    <icon
                        type="help"
                        viewBox="0 0 17 17"
                        height="16"
                        width="16"
                        class="cursor-pointer text-60 -mb-1"
                    />

                    <tooltip-content
                        slot="content"
                        v-html="helpText"
                        :max-width="helpWidth"
                    />
                </tooltip>
            </div>

            <select
                v-if="ranges.length > 0"
                @change="handleChange"
                class="select-box-sm ml-auto min-w-24 h-6 text-xs appearance-none bg-40 pl-2 pr-6 active:outline-none active:shadow-outline focus:outline-none focus:shadow-outline"
            >
                <option
                    v-for="option in ranges"
                    :key="option.value"
                    :value="option.value"
                    :selected="selectedRangeKey == option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <div v-if="events.length > 0" class="overflow-hidden overflow-y-auto max-h-90px">
            <div v-for="event in events" :key="event.key" class="flex items-top mb-px">
                <div class="w-8 flex flex-col items-center justify-center mr-2">
                    <div v-text="moment(event.date).format('MMM')" class="text-xs text-70 font-italics"/>
                    <div class="w-8 border-2 border-t-4 border-solid rounded border-70 text-center">
                        <div v-text="moment(event.date).format('D')" class="text-xl font-bold text-70"/>
                    </div>
                </div>
                <div class="mt-2 flex-1">
                    <div class="pl-2 border-l border-70">
                        <div v-text="event.subject" class="font-bold text-80" :class="{'leading-loose py-1': !event.description}"/>
                        <div v-text="event.description" class="text-sm text-90"/>
                    </div>
                </div>
                <div class="min-w-24 mt-2 -mr-2 text-center">
                    <div v-text="formattedValue(event.value)" class="text-xl"/>
                    <div v-if="event.value != null" v-text="formattedSuffix(event.value)" class="text-xs text-70"/>
                </div>
            </div>
        </div>
        <div v-else class="text-4xl text-70">
            {{ __('No Data') }}
        </div>
    </loading-card>
</template>

<script>
import numbro from 'numbro'
import moment from 'moment'
import numbroLanguages from 'numbro/dist/languages.min'
Object.values(numbroLanguages).forEach(l => numbro.registerLanguage(l))
import _ from 'lodash'
import { SingularOrPlural } from 'laravel-nova'

export default {
    name: 'BaseCalendarEventsMetric',

    props: {
        loading: Boolean,
        title: {},
        helpText: {},
        helpWidth: {},
        events: {},
        prefix: '',
        suffix: '',
        suffixInflection: true,
        ranges: { type: Array, default: () => [] },
        selectedRangeKey: [String, Number],
        format: {
            type: String,
            default: '0[.]00a',
        },
    },

    mounted() {
        if (Nova.config.locale) {
            numbro.setLanguage(Nova.config.locale.replace('_', '-'))
        }
    },

    methods: {
        handleChange(event) {
            this.$emit('selected', event.target.value)
        },
        formattedValue(value) {

            if(value == null) {
                return '';
            }

            let result = numbro(new String(value)).format(this.format);

            return `${this.prefix}${result}`;

        },

        formattedSuffix(value) {

            if(this.suffixInflection === false) {
                return this.suffix;
            }

            return SingularOrPlural(value, this.suffix);

        },

        moment(value) {
            return moment(value, 'YYYY-MM-DD');
        }
    }
}
</script>
