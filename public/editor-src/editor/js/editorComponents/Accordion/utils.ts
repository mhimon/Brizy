interface Config {
  duration: string;
  height: number;
  onFinish?: VoidFunction;
}

export const expand = (node: HTMLElement, config: Config): void => {
  const { height, duration, onFinish } = config;
  const animation = node.animate(
    [
      { height: `${height}px`, offset: 0 },
      { height: 0, offset: 1 }
    ],
    {
      duration,
      iterations: 1,
      fill: "forwards" // ensures item stays open at end of animation
    }
  );
  if (typeof onFinish === "function") {
    animation.onfinish = onFinish;
  }
};

export const collapse = (node: HTMLElement, config: Config): void => {
  const { height, duration, onFinish } = config;
  const animation = node.animate(
    [
      { height: 0, offset: 0 },
      { height: `${height}px`, offset: 1 },
      { height: "auto" }
    ],
    {
      duration,
      iterations: 1,
      fill: "forwards" // ensures item stays open at end of animation
    }
  );
  if (typeof onFinish === "function") {
    animation.onfinish = onFinish;
  }
};
