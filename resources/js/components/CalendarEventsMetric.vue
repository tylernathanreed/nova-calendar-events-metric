<template>
    <BaseCalendarEventsMetric
        @selected="handleRangeSelected"
        :title="card.name"
        :help-text="card.helpText"
        :help-width="card.helpWidth"
        :events="data"
        :ranges="card.ranges"
        :format="format"
        :prefix="prefix"
        :suffix="suffix"
        :suffix-inflection="suffixInflection"
        :selected-range-key="selectedRangeKey"
        :loading="loading"
    />
</template>

<script>
import _ from 'lodash'
import { InteractsWithDates, Minimum } from 'laravel-nova'
import BaseCalendarEventsMetric from './Base/CalendarEventsMetric'

export default {
    name: 'CalendarEventsMetric',

    mixins: [InteractsWithDates],

    components: {
        BaseCalendarEventsMetric,
    },

    props: {
        card: {
            type: Object,
            required: true,
        },

        resourceName: {
            type: String,
            default: '',
        },

        resourceId: {
            type: [Number, String],
            default: '',
        },

        lens: {
            type: String,
            default: '',
        },
    },

    data: () => ({
        loading: true,
        value: '',
        data: [],
        format: '(0[.]00a)',
        prefix: '',
        suffix: '',
        suffixInflection: true,
        selectedRangeKey: null,
    }),

    watch: {
        resourceId() {
            this.fetch()
        },
    },

    created() {
        if (this.hasRanges) {
            this.selectedRangeKey = this.card.ranges[0].value
        }

        if (this.card.refreshWhenActionRuns) {
            Nova.$on('action-executed', () => this.fetch())
        }

        if (this.card.refreshWhenFilterChanged) {
            Nova.$on('filter-changed', () => this.$nextTick(() => setTimeout(() => this.fetch(), 33)))
        }
    },

    mounted() {
        this.fetch()
    },

    methods: {
        handleRangeSelected(key) {
            this.selectedRangeKey = key
            this.fetch()
        },

        fetch() {
            this.loading = true

            Minimum(Nova.request().get(this.metricEndpoint, this.getMetricPayload())).then(
                ({
                    data: {
                        value: {
                            events,
                            prefix,
                            suffix,
                            suffixInflection,
                            format,
                        },
                    },
                }) => {
                    this.data = events
                    this.format = format || this.format
                    this.prefix = prefix || this.prefix
                    this.suffix = suffix || this.suffix
                    this.suffixInflection = suffixInflection
                    this.loading = false
                }
            )
        },

        getMetricPayload() {
            const payload = {
                params: {
                    timezone: this.userTimezone,
                    twelveHourTime: this.usesTwelveHourTime,
                    referrerName: Nova.app.$route.name,
                    referrerQuery: Nova.app.$route.query
                },
            }

            if (this.hasRanges) {
                payload.params.range = this.selectedRangeKey
            }

            return payload
        },
    },

    computed: {
        hasRanges() {
            return this.card.ranges.length > 0
        },

        metricEndpoint() {
            const lens = this.lens !== '' ? `/lens/${this.lens}` : ''
            if (this.resourceName && this.resourceId) {
                return `/nova-api/${this.resourceName}${lens}/${this.resourceId}/metrics/${this.card.uriKey}`
            } else if (this.resourceName) {
                return `/nova-api/${this.resourceName}${lens}/metrics/${this.card.uriKey}`
            } else {
                return `/nova-api/metrics/${this.card.uriKey}`
            }
        },
    },
}
</script>
