<template>
  <div class="red">
      <button @click="addpf">add portfolio</button>
      <button @click="rmpf">remove portfolio</button>
      <button @click="addp">add position</button>
      <button @click="rmp">remove position</button>
      <button @click="switchp">switch position</button>
      <button @click="editp">edit position</button>
      <pre>
          {{ JSON.stringify(msg, null, 4) }}
      </pre>
  </div>
</template>
<script>
export default {
    data() {
        return {
            msg: reactivePortfolio
        }
    },
    methods: {
        addp() {
            addPosition(reactivePortfolio[0].uid, positionFactory(114, 10, 100000));
        },
        rmp() {
            removePosition(reactivePortfolio[0].uid, reactivePortfolio[0].positions[0].uid);
        },
        addpf() {
            addPortfolio('hello');
        },
        rmpf() {
            removePortfolio(reactivePortfolio[0].uid);
        },
        switchp() {
            movePosition(reactivePortfolio[0].uid, reactivePortfolio[0].positions[0].uid, reactivePortfolio[1].uid);
        },
        editp() {
            editPosition(reactivePortfolio[0].uid,
                reactivePortfolio[0].positions[0].uid,
                reactivePortfolio[0].positions[0].qty + 1,
                reactivePortfolio[0].positions[0].mark + 1)
        }
    }
}
</script>
<style scoped>
.red {
  color: red;
}
</style>