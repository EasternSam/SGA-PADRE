"use client"
import { cn } from '@/lib/utils';
import { motion, useInView } from 'framer-motion';
import * as React from 'react';

export function WordsPullUp({
  text = '',
  className = '',
  wrapperClassName = '',
}) {
  const splittedText = (text || '').split(' ');

  const pullupVariant = {
    initial: { y: 20, opacity: 0 },
    animate: (i) => ({
      y: 0,
      opacity: 1,
      transition: {
        delay: i * 0.1,
      },
    }),
  };
  const ref = React.useRef(null);
  const isInView = useInView(ref, { once: true });
  return (
    <div ref={ref} className={cn("flex justify-center", wrapperClassName)}>
      {splittedText.map((current, i) => (
        <motion.div
          key={i}
          ref={ref}
          variants={pullupVariant}
          initial="initial"
          animate={isInView ? 'animate' : ''}
          custom={i}
          className={cn(
            'text-xl text-center sm:text-4xl font-bold tracking-tighter md:text-6xl md:leading-[4rem]',
            'pr-2', // class to separate words
            className
          )}
        >
          {current == '' ? <span>&nbsp;</span> : current}
        </motion.div>
      ))}
    </div>
  );
}

export default WordsPullUp;
