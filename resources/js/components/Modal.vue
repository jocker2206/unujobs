<template>
    <ventana v-if="show" :index="index">
        <div :class="col">
            <div class="card" :style="`height: ${height}`">
                <div class="card-header">

                    <slot name="header"></slot>
                    
                    <button class="close" v-on:click="close">
                        <small><i class="fas fa-times"></i></small>
                    </button>
                </div>


                <slot name="content"></slot>
                
            </div>
        </div>
    </ventana>
</template>

<script>
export default {
    props: {
        height: {
            type: String,
            default: "70vh"
        },
        col: {
            type: String,
            default: "col-md-5"
        },
        show: {
            type: Boolean,
            default: true
        },
        index: {
            type: String,
            default: '90'
        }
    },  
    methods: {
        close() {
            this.$emit("close");
        }
    }
}
</script>

