<template>
  <div class="tree-root">
    <div class="context-tree__property-list">
      <dl class="object-features_location-dl" :key="'context-parts__-' + context.identifier + index"
          v-for="(contextPart, index) in contextParts">
        <dt>{{ contextPart.title }}</dt>
        <dd>{{ contextPart.value }}</dd>
      </dl>
    </div>

    <div @click="handleToggleCollapsed()" class="item-line" v-if="context.context">
      <div>{{ collapsed ? 'Bekijk omvattende context' : 'Verberg omvattende context'}}</div>
      <div class="ui mini icon" style="margin-left: 1rem;">
        <i class="eye icon" v-if="collapsed"></i>
        <i class="eye slash icon" v-else></i>
      </div>
    </div>

    <template v-if="!collapsed && context.context">
      <div class="children">
        <context-tree :context="context.context" :excavation="excavation" :key="'typology__' + context.context.identifier"/>
      </div>
    </template>
  </div>
</template>

<script>
  export default {
    props: {
      context: Object,
      isRoot: {
        type: Boolean,
        default: false,
      },
      excavation: Object
    },
    name: "ContextTree.vue",
    data() {
      return {
        collapsed: true,
      }
    },
    computed: {
      contextDating() {
        if (!this.context) {
          return
        }

        if (!this.context.contextDating || !this.context.contextDating.contextDatingPeriod) {
          return
        }

        var dating = this.context.contextDating.contextDatingPeriod

        var datingMetaData = ''

        if (this.context.contextDating.contextDatingTechnique) {
          if (this.context.contextDating.contextDatingTechnique.contextDatingPeriodPrecision) {
            datingMetaData += this.context.contextDating.contextDatingTechnique.contextDatingPeriodPrecision + ', '
          }

          if (this.context.contextDating.contextDatingTechnique.contextDatingPeriodNature) {
            datingMetaData += this.context.contextDating.contextDatingTechnique.contextDatingPeriodNature + ', '
          }

          if (this.context.contextDating.contextDatingTechnique.contextDatingPeriodMethod) {
            datingMetaData += this.context.contextDating.contextDatingTechnique.contextDatingPeriodMethod
          }
        }

        return dating + ' (' + datingMetaData + ')'
      },
      contextLegacyId () {
        if (this.context.contextLegacyId && this.context.contextLegacyId.contextLegacyIdValue && typeof this.context.contextLegacyId.contextLegacyIdValue === 'string') {
          return this.context.contextLegacyId.contextLegacyIdValue
        }
      },
      contextParts() {
        var parts = []

        if (!this.context) {
          return parts
        }

        parts.push({
          title: 'Context',
          value: this.contextLegacyId ? this.contextLegacyId + ' (' + this.context.contextType + ')' : this.context.contextType
        })

        // If the context is the last one, add the excavation details
        if (!this.context.context) {
          parts.push({
            title: 'Opgraving',
            value: this.excavation.excavationTitle + ', ' + this.excavation.excavationPeriod
          })

          parts.push({
            title: 'OpgravingsID',
            value: this.excavation.excavationID + ' (' + this.excavation.excavationIDType + ')'
          })
        }

        parts.push({
          title: 'Contextnummer',
          value: this.context.contextLegacyId && this.context.contextLegacyId.contextLegacyIdValue && this.context.contextLegacyId instanceof String
        })

        parts.push({
          title: 'Datering context',
          value: this.contextDating
        })

        parts.push({
          title: 'Opmerking bij datering',
          value: this.context.contextDating && this.context.contextDating.contextDatingRemark
        })

        parts.push({
          title: 'Karakter context',
          value: this.context.contextCharacter && this.context.contextCharacter.contextCharacterType
        })

        parts.push({
          title: 'Interpretatie',
          value: this.context.contextInterpretation
        })

        return parts.filter(part => part.value)
      },
      treeStyle() {
        if (this.isRoot || !this.shouldCollapse) {
          return {}
        }

        return {visibility: 'hidden', height: '0px', border: '0px', padding: '0px'}
      },
      shouldCollapse () {
        return this.collapsed
      },
      children() {
        if (!this.root.childrenCodes) {
          return []
        }

        return Object.values(this.root.childrenCodes)
      },
    },
    methods: {
      handleToggleCollapsed() {
        this.collapsed = !this.collapsed
      },
    }
  }
</script>

<style lang="scss" scoped>
  .hiddenItem {
    visibility: hidden;
  }

  .object-features_object-dl {
    dd {
      margin-left: calc(150px + 1rem);
    }
  }

  .object-features_location-dl {
    dd {
      margin-left: calc(170px + 1rem);
    }
  }

  .children {
    padding-left: 1rem;
    border-left: 1px solid rgba(0, 0, 0, .5);
    -webkit-background-clip: padding-box;
    background-clip: padding-box;
    margin-left: 0.15rem;
  }

  .tree-root {
    border: 1px solid transparent;
    padding-bottom: 5px;
    user-select: none;
  }

  .accepting-drop {
    border: 1px solid #00C3AF;

    > .item-line > .name-span {
      color: #00C3AF;
    }
  }

  .name-input {
    font-size: inherit;
    padding: 0 6px;
    position: relative;
    left: -7px;
    width: 300px;
  }

  .hidden {
    visibility: hidden;
  }

  .item-line {
    display: flex;
    height: 100%;
    overflow: auto;
    line-height: 24px;
  }

  .item-line {
    .buttons {
      display: none;
    }

    &:hover {
      background-color: #f3f3f3;

      .buttons {
        display: block;
      }
    }

    label {
      display: flex;
      margin-top: 3px;
    }
  }

  .icon {
    cursor: pointer;
    opacity: 0.5;

    &:hover {
      opacity: 1;
    }
  }

  .tree__item-description {
    margin-left: 10px;
    color: #aaa;
    font-size: 85%;
    margin-bottom: 10px;
    margin-top: -3px;
  }

  .tree__main-item-description {
    font-weight: 500;
  }

  .tree__item-cta {
    font-size: 10px;
    line-height: 10px;
    padding: 3px;
    height: fit-content;
    top: 0.3em;
    margin-left: 0.8em;
  }
</style>
