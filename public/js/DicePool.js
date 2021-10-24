// dice roler library

var DicePool = function () {
    this.container = [] // FIFO
    this.diceCount = 100
}

DicePool.prototype.rollOne = function (side) {
    var self = this

    if (this.container.length === 0) {
        return new Promise(function (fulfill, reject) {
            fetch('https://www.random.org/integers/?num='
                    + self.diceCount
                    + '&min=1&max=120&col='
                    + self.diceCount
                    + '&base=10&format=plain&rnd=new').then(function (response) {
                return response.text()
            }).then(function (content) {
                //console.log("load")

                var extracted = content.split("\t");
                for (var k = 0; k < self.diceCount; k++) {
                    self.container[k] = parseInt(extracted[k]);
                }

                self.rollOne(side).then(function (r) {
                    fulfill(r)
                })
            })
        })
    } else {
        return new Promise(function (fulfill, reject) {
            var r = self.container.shift()
            fulfill(1 + r % side)
        })

    }
}

DicePool.prototype.recursiveRollOne = function (side) {
    var self = this

    return new Promise(function (fulfill, reject) {
        self.rollOne(side).then(function (first) {
            //console.log("during recurs " + first)
            if (first < side) {
                fulfill(first)
            } else {
                //console.log('reroll')
                self.recursiveRollOne(side).then(function (reroll) {
                    fulfill(first + reroll)
                })
            }
        })
    })
}

DicePool.prototype.rollPool = function (pool) {  /// array of sides
    var self = this

    return new Promise(function (fulfill, reject) {
        if (pool.length === 0) {
            fulfill([])
        } else {
            var head = pool.shift()
            self.recursiveRollOne(head).then(function (first) {
                self.rollPool(pool).then(function (result) {
                    result.unshift(first)
                    fulfill(result)
                })
            })
        }
    })
}