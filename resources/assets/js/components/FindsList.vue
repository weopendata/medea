<template>
    <table class="table">
        <thead>
            <tr>
                <th v-for="column in columns"
                @click="sortBy(column.objectKey)">
                @{{column.displayName | capitalize}}
                <span class="arrow"
                :class="sortOrders[column.objectKey] > 0 ? 'asc' : 'dsc'">
                </span>
                </th>
                <th v-show="viewRole == 'Detectorist/Registrator'"></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
          <tr v-for="entry in finds">
              <td v-for="column in columns">
                @{{entry[column.objectKey]}}
            </td>
            <td>
                <a href="finds/15"><span class="fa fa-eye"></span></a>
            </td>

        </tr>
    </tbody>
</table>
</template>

<script>
    export default {

        props : ['columns', 'finds'],

        ready () {
            alert("lets do this");
        }
    }

</script>