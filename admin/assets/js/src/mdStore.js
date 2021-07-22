
const initialState = {
    showComments: true,
};

const actions = {
    setShowComments(showComments = true) {
        return {
            type: "showComments",
            payload : showComments
        };
    },

    setDataText(datatext = '') {
        return {
            type: "DataText",
            payload : datatext
        };
    },
};

const mdStore = {
    reducer(state = initialState , action){
        if(action.type === 'showComments'){
            return{ ...state, showComments: action.payload }
        }
        if(action.type === 'DataText'){
            return{ ...state, datatext: action.payload }
        }
        return state;
    },

selectors: {
    getShowComments( state ) {
      
        return state.showComments;
    },

    getDataText(state){
        return state.datatext ? state.datatext : null; 
    }
   
},
actions: actions,
};
wp.data.registerStore('mdstore',mdStore);

wp.data.subscribe(() =>{
    const showcomment = wp.data.select('mdstore').getShowComments();
    const showDatatext = wp.data.select('mdstore').getDataText();
 
   
});
