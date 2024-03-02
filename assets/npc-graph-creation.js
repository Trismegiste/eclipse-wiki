/*
 * eclipse-wiki
 */
export default () => ({
        graph: null,
        choices: null,
        baseUrl: {
            picture: null,
            name: null
        },
        keywords: {},
        avatar: [],
        seekNode: function (key) {
            const found = this.graph.find(node => {
                return node.name === key
            })
            if (typeof found === 'undefined') {
                throw new Error('The node named "' + key + '" is unknown')
            } else {
                return found
            }
        },
        setChoiceAt: function (i, key) {
            this.choices.splice(i)
            this.choices[i] = key
        },
        getAttributes: function () {
            let cumulative = {}
            for (const [key, value] of Object.entries(this.flattenObjectWithSum('attributes'))) {
                cumulative[key] = 4 + 2 * value
            }
            return cumulative
        },
        getSkills: function () {
            let cumulative = {}
            for (const [key, value] of Object.entries(this.flattenObjectWithSum('skills'))) {
                cumulative[key] = 2 + 2 * value
            }
            return cumulative
        },
        getNetworks: function () {
            return this.flattenObjectWithSum('networks')
        },
        getEdges: function () {
            return this.flattenArrayWithUnique('edges')
        },
        getBackgrounds: function () {
            return this.flattenArrayWithUnique('backgrounds')
        },
        getFactions: function () {
            return this.flattenArrayWithUnique('factions')
        },
        getMorphs: function () {
            return this.flattenArrayWithUnique('morphs')
        },
        getText2img: function () {
            return this.flattenArrayWithUnique('text2img')
        },
        flattenObjectWithSum: function (propertyKey) {
            let cumulative = {}
            for (let choice of this.choices) {
                let bonus = this.seekNode(choice)
                for (const [key, value] of Object.entries(bonus[propertyKey])) {
                    if (!cumulative.hasOwnProperty(key)) {
                        cumulative[key] = 0
                    }
                    cumulative[key] += value
                }
            }
            return cumulative
        },
        flattenArrayWithUnique: function (propertyKey) {
            let cumulative = new Set()
            for (let choice of this.choices) {
                let bonus = this.seekNode(choice)
                for (const value of bonus[propertyKey]) {
                    cumulative.add(value)
                }
            }
            return Array.from(cumulative.values())
        },
        updateAvatar: function () {
            this.avatar = []
            let query = ''
            for (let key of this.getText2img()) {
                if (this.keywords[key]) {
                    query += key + ' '
                }
            }
            query.trim()
            fetch(this.baseUrl.picture + '?q=' + query)
                    .then(resp => {
                        return resp.json()
                    })
                    .then(result => {
                        this.avatar = result
                    })
        },
        generateName: function () {
            let gender = (this.choices.includes('homme')) ? 'male' : 'female'
            fetch(this.baseUrl.name + '?gender=' + gender + '&language=' + this.$refs.select_language.value)
                    .then(resp => {
                        return resp.json()
                    })
                    .then(result => {
                        this.$refs.fullname.value = result
                    })
        }
    })
