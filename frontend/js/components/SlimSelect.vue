<template>
  <div class="vselectOuter">
    <a17-inputframe :error="error" :label="label" :note="note" :size="size" :name="name" :label-for="uniqId" :required="required" :add-new="addNew">
      <div class="slimselect" :class="extraClasses">
        <div class="vselect__field">
          <select
            ref="input"
            :multiple="multiple"
            :placeholder="placeholder"
            :required="required"
          >
            <template v-if="this.optgroup">
              <optgroup v-for="group in groups" :key="group" :label="group">
                <option v-for="option in groupOptions(group)" :key="option.value" :selected="isSelected(option.value)" :value="option.value">
                  {{ option.value ? option.label : placeholder }}
                </option>
              </optgroup>
            </template>
            <template v-else>
              <option v-for="option in currentOptions" :key="option.value" :selected="isSelected(option.value)" :value="option.value">
                {{ option.value ? option.label : placeholder }}
              </option>
            </template>
          </select>
        </div>
      </div>
    </a17-inputframe>
    <template v-if="addNew">
      <a17-modal-add ref="addModal" :name="name" :form-create="addNew" :modal-title="'Add new ' + label">
        <slot name="addModal"></slot>
      </a17-modal-add>
    </template>
  </div>
</template>

<script>
  import randKeyMixin from '@/mixins/randKey'
  import FormStoreMixin from '@/mixins/formStore'
  import InputframeMixin from '@/mixins/inputFrame'
  import AttributesMixin from '@/mixins/addAttributes'
  import SlimSelect from 'slim-select'
  import 'slim-select/dist/slimselect.min.css'

  export default {
    name: 'A17SlimSelect',
    mixins: [randKeyMixin, InputframeMixin, FormStoreMixin, AttributesMixin],
    props: {
      placeholder: {
        type: String,
        default: ''
      },
      disabled: {
        type: Boolean,
        default: false
      },
      name: {
        type: String,
        default: ''
      },
      transition: {
        type: String,
        default: 'fade_move_dropdown'
      },
      multiple: {
        type: Boolean,
        default: false
      },
      taggable: { // Enable/disable creating options from searchInput.
        type: Boolean,
        default: false
      },
      pushTags: { // When true, newly created tags will be added to the options list.
        type: Boolean,
        default: false
      },
      searchable: {
        type: Boolean,
        default: false
      },
      clearSearchOnSelect: {
        type: Boolean,
        default: true
      },
      selected: {
        default: null
      },
      largeItems: {
        type: Boolean,
        default: false
      },
      showIcons: {
        type: Boolean,
        default: false
      },
      emptyText: {
        default () {
          return this.$trans('select.empty-text', 'Sorry, no matching options.')
        }
      },
      options: {
        default: function () { return [] }
      },
      optionsLabel: { // label in vueselect
        type: String,
        default: 'label'
      },
      optgroup: {
        type: String,
        default: null
      },
      endpoint: {
        type: String,
        default: ''
      },
      size: {
        type: String,
        default: '' // 'small', 'large'
      },
      required: {
        type: Boolean,
        default: false
      },
      isHtml: {
        type: Boolean,
        default: false
      },
      closeOnSelect: {
        type: Boolean,
        default: true
      },
      allowDeselectOption: {
        type: Boolean,
        default: false
      },
      maxHeight: { // max-height of the dropdown menu
        type: String,
        default: '400px'
      }
    },

    data: function () {
      return {
        value: this.selected,
        currentOptions: this.options,
        ajaxUrl: this.endpoint,
        slimSelect: null
      }
    },
    watch: {
      options: function (options) {
        this.currentOptions = this.options
      }
    },
    mounted () {
      const data = {
        select: this.$refs.input,
        placeholder: this.placeholder,
        closeOnSelect: this.closeOnSelect,
        allowDeselectOption: this.allowDeselectOption,
        onChange: (data) => {
          this.updateValue(this.slimSelect.selected())
        }
      }
      if (this.isHtml) {
        data.data = []
        for (const group in this.groups) {
          const dataItem = { label: this.groups[group] ? this.groups[group] : '', options: [] }
          const groupItems = this.groupOptions(this.groups[group])
          for (const item in groupItems) {
            dataItem.options.push({
              innerHTML: groupItems[item].html,
              text: groupItems[item].label,
              value: groupItems[item].value,
              selected: this.isSelected(groupItems[item].value)
            })
          }
          data.data.push(dataItem)
        }
      }
      this.slimSelect = new SlimSelect(data) // eslint-disable-line
    },
    computed: {
      uniqId: function (value) {
        return this.name + '-' + this.randKey
      },
      groups: function () {
        return [...new Set(this.currentOptions.map(i => i[this.optgroup]))]
      },
      extraClasses: function () {
        const classes = []
        if (this.largeItems) {
          classes.push('large-items')
        }
        if (this.showIcons) {
          classes.push('show-icons')
        }
        return classes.join(' ')
      },
      inputValue: {
        get: function () {
          if (this.value) {
            if (!this.multiple) { // single selects
              if (typeof this.value === 'object') {
                return this.value.value
              }
            } else { // multiple selects
              if (Array.isArray(this.value)) {
                if (typeof this.value[0] === 'object') {
                  return this.value.map(e => e.value)
                }

                return this.value.join(',')
              }
            }
            return this.value
          } else {
            return ''
          }
        },
        set: function (value) {
          if (Array.isArray(value)) {
            if (this.taggable) {
              this.value = value
            } else {
              this.value = this.options.filter(o => value.includes(o.value))
            }
          } else {
            this.value = this.options.find(o => o.value === value)
          }
        }
      }
    },
    methods: {
      updateFromStore: function (newValue) { // called from the formStore mixin
        this.inputValue = newValue
      },
      groupOptions: function (group) {
        return this.options.filter(i => i[this.optgroup] === group)
      },
      isSelected: function (value) {
        if (!value) {
          return false
        }
        if (Array.isArray(this.inputValue)) {
          return this.inputValue.indexOf(value) !== -1
        }
        return value === this.inputValue
      },
      isAjax: function () {
        return this.ajaxUrl !== ''
      },
      updateValue: function (value) {
        // see formStore mixin
        this.value = value
        this.saveIntoStore(this.multiple ? value : this.inputValue)

        this.$emit('change', value)
      }
    }
  }
</script>

<style lang="scss">

.slimselect {

  .ss-main {
    color: inherit;

    .ss-single-selected, .ss-multi-selected {
      @include textfield;
      @include defaultState;
      padding:0 15px;
      min-height: 43px;

      &.s--focus {
        @include focusState;
      }

      &:hover {
        @include focusState;
      }

      &.s--disabled {
        @include disabledState;
      }
    }
  }

  .ss-main .ss-multi-selected {
    padding: 0 6px;

    .ss-values .ss-value {
        background-color: $color__ok;
        color: white;
        font-weight: 600;
        justify-content: space-between;
        font-size: 14px;
        padding: 8px 12px;
    }
  }

  .ss-main .ss-multi-selected .ss-add {
    margin: 0px 12px 0 5px;
    justify-content: center;
    align-items: center;
  }

  &.large-items {

    .ss-main .ss-multi-selected .ss-values .ss-value {
        width: 100%;
    }

    .ss-main .ss-multi-selected .ss-values .ss-value .ss-value-delete {
        margin-right: 5px;
    }
  }

  &.show-icons {

    .ss-main .ss-single-selected {
      svg {
        height: 22px !important;
        margin-right: 10px;
      }
    }

    .ss-content {
      background: #e5e5e5;
    }

    .ss-content .ss-list {
      display: flex;
      flex-wrap: wrap;
      max-height: 250px;

      .ss-optgroup {
        width: 100%;
        display: flex;
        flex-wrap: wrap;

        .ss-optgroup-label {
          width: 100%;
        }

        .ss-option {
          width: 16.66%;
          text-align: center;
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 6px;
          height: 100px;
          flex-direction: column;

          div {
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 10px;
            max-width: 100%;
          }

          .icon {
            svg {
              height: 35px !important;
              margin-bottom: 10px;
            }
          }
        }
      }

      .ss-content .ss-list .ss-option {
          width: 20%;
      }
    }
  }
}

</style>
