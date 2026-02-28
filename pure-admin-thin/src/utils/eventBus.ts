import mitt from "mitt";

type Events = {
  "company-change": number;
  "store-change": number;
};

const emitter = mitt<Events>();

export default emitter;
