<template>
    <div class="block-locales">
      <a17-multiselect
            label="Languages availables"
            :options="languages"
            :selected="values"
            :grid="true"
            :inline="false"
            @change="updateLang"
        >
        </a17-multiselect>
    </div>
</template>

<script>
  import { CONTENT } from '@/store/mutations'
  import LocaleMixin from '@/mixins/locale'

  export default {
    name: 'A17LangBlock',
    mixins: [LocaleMixin],
    data: function () {
      return {
        values: []
      }
    },
    mounted () {
      if (this.$parent.block.locales.value) {
        this.values = this.$parent.block.locales.value
      }
    },
    methods: {
      updateLang (data) {
        this.$store.commit(CONTENT.UPDATE_LOCALES_BLOCK, {
          block: this.$parent.block,
          index: this.$parent.index,
          locales: data
        })
      }
    }
  }
</script>
<style lang="scss" scoped>
    .block-locales  {
        padding-bottom: 2em;
    }
</style>
