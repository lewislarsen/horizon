<script type="text/ecmascript-6">
import LineChart from '../../components/LineChart.vue';

export default {
  components: { LineChart },
  data() {
    return {
      ready: false,
      rawData: {},
      metric: {}
    };
  },
  mounted() {
    document.title = "Horizon - Metrics";
    this.loadMetric();
  },
  methods: {
    loadMetric() {
      this.ready = false;
      this.$http.get(Horizon.basePath + '/api/metrics/' + this.$route.params.type + '/' + encodeURIComponent(this.$route.params.slug))
          .then(response => {
            let data = this.prepareData(response.data);
            this.rawData = response.data;
            this.metric.throughPutChart = this.buildChartData(data, 'throughput', 'Times');
            this.metric.runTimeChart = this.buildChartData(data, 'runtime', 'Seconds');
            this.ready = true;
          });
    },
    prepareData(data) {
      return Object.values(this.groupBy(data.map(value => ({
        ...value,
        time: this.formatDate(value.time).format("MMM-D hh:mmA"),
      })), 'time')).map(value => value.reduce((sum, value) => ({
        runtime: parseFloat(sum.runtime) + parseFloat(value.runtime),
        throughput: parseInt(sum.throughput) + parseInt(value.throughput),
        time: value.time
      })))
    },
    buildChartData(data, attribute, label) {
      return {
        labels: data.map(entry => entry.time),
        datasets: [{
          label: label,
          data: data.map(entry => entry[attribute]),
          lineTension: 0,
          backgroundColor: 'rgba(119, 70, 236, 0.1)',
          pointBackgroundColor: '#fff',
          pointBorderColor: '#7746ec',
          borderColor: '#7746ec',
          borderWidth: 2,
          fill: true
        }],
      };
    },
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4 flex items-center justify-between">
        <h3 class="font-medium text-strong">Throughput — {{ $route.params.slug }}</h3>
      </div>
      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <div v-if="!ready" class="flex justify-center py-10">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-6 text-icon-alpha animate-spin fill-current">
            <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
          </svg>
        </div>
        <p v-if="ready && !rawData.length" class="text-center text-weak py-10">Not Enough Data</p>
        <line-chart v-if="ready && rawData.length" :data="metric.throughPutChart" />
      </div>
    </div>

    <div class="overflow-hidden shadow-inner p-1 bg-weak rounded-xl">
      <div class="px-5 py-4 flex items-center justify-between">
        <h3 class="font-medium text-strong">Runtime — {{ $route.params.slug }}</h3>
      </div>
      <div class="bg-default shadow-xs-with-border rounded-lg p-5">
        <div v-if="!ready" class="flex justify-center py-10">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="size-6 text-icon-alpha animate-spin fill-current">
            <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
          </svg>
        </div>
        <p v-if="ready && !rawData.length" class="text-center text-weak py-10">Not Enough Data</p>
        <line-chart v-if="ready && rawData.length" :data="metric.runTimeChart" />
      </div>
    </div>
  </div>
</template>